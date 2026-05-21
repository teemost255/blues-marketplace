import "./lib/error-capture";

import { consumeLastCapturedError } from "./lib/error-capture";
import { renderErrorPage } from "./lib/error-page";
import { supabaseAdmin } from "@/integrations/supabase/client.server";

type ServerEntry = {
  fetch: (request: Request, env: unknown, ctx: unknown) => Promise<Response> | Response;
};

let serverEntryPromise: Promise<ServerEntry> | undefined;

async function getServerEntry(): Promise<ServerEntry> {
  if (!serverEntryPromise) {
    serverEntryPromise = import("@tanstack/react-start/server-entry").then(
      (m) => ((m as { default?: ServerEntry }).default ?? (m as unknown as ServerEntry)),
    );
  }
  return serverEntryPromise;
}

function brandedErrorResponse(): Response {
  return new Response(renderErrorPage(), {
    status: 500,
    headers: { "content-type": "text/html; charset=utf-8" },
  });
}

function isCatastrophicSsrErrorBody(body: string, responseStatus: number): boolean {
  let payload: unknown;
  try {
    payload = JSON.parse(body);
  } catch {
    return false;
  }

  if (!payload || Array.isArray(payload) || typeof payload !== "object") {
    return false;
  }

  const fields = payload as Record<string, unknown>;
  const expectedKeys = new Set(["message", "status", "unhandled"]);
  if (!Object.keys(fields).every((key) => expectedKeys.has(key))) {
    return false;
  }

  return (
    fields.unhandled === true &&
    fields.message === "HTTPError" &&
    (fields.status === undefined || fields.status === responseStatus)
  );
}

// h3 swallows in-handler throws into a normal 500 Response with body
// {"unhandled":true,"message":"HTTPError"} — try/catch alone never fires for those.
async function normalizeCatastrophicSsrResponse(response: Response): Promise<Response> {
  if (response.status < 500) return response;
  const contentType = response.headers.get("content-type") ?? "";
  if (!contentType.includes("application/json")) return response;

  const body = await response.clone().text();
  if (!isCatastrophicSsrErrorBody(body, response.status)) {
    return response;
  }

  console.error(consumeLastCapturedError() ?? new Error(`h3 swallowed SSR error: ${body}`));
  return brandedErrorResponse();
}

async function handleAdminSyncAuth(request: Request): Promise<Response> {
  const jsonHeaders = { "content-type": "application/json; charset=utf-8" };

  if (request.method !== "POST") {
    return new Response(JSON.stringify({ success: false, error: "Method not allowed" }), {
      status: 405,
      headers: jsonHeaders,
    });
  }

  let body: unknown;
  try {
    body = await request.json();
  } catch {
    return new Response(JSON.stringify({ success: false, error: "Invalid JSON payload" }), {
      status: 400,
      headers: jsonHeaders,
    });
  }

  const { email, password } = body as { email?: string; password?: string };
  if (!email || typeof email !== "string" || !password || typeof password !== "string") {
    return new Response(JSON.stringify({ success: false, error: "Email and password are required" }), {
      status: 400,
      headers: jsonHeaders,
    });
  }

  const normalizedEmail = email.toLowerCase();
  const { data: admin, error: verifyError } = await supabaseAdmin
    .rpc("verify_admin_password", {
      p_email: normalizedEmail,
      password,
    })
    .single();

  if (verifyError || !admin || !admin.is_valid) {
    return new Response(JSON.stringify({ success: false, error: "Invalid admin credentials" }), {
      status: 401,
      headers: jsonHeaders,
    });
  }

  const { data: userList, error: listError } = await supabaseAdmin.auth.admin.listUsers({ perPage: 1000 });
  if (listError) {
    console.error("Admin sync listUsers error:", listError);
    return new Response(JSON.stringify({ success: false, error: "Unable to sync admin user" }), {
      status: 500,
      headers: jsonHeaders,
    });
  }

  const existingAuthUser = userList.users.find((user: any) => user.email?.toLowerCase() === normalizedEmail);
  let authUserId: string | undefined;

  if (existingAuthUser) {
    authUserId = existingAuthUser.id;
    const { error: updateError } = await supabaseAdmin.auth.admin.updateUserById(authUserId, {
      password,
    });
    if (updateError) {
      console.error("Admin sync updateUserById error:", updateError);
      return new Response(JSON.stringify({ success: false, error: "Unable to update admin auth user" }), {
        status: 500,
        headers: jsonHeaders,
      });
    }
  } else {
    const { data: createData, error: createError } = await supabaseAdmin.auth.admin.createUser({
      email: normalizedEmail,
      password,
      email_confirm: true,
    });
    if (createError || !createData?.user?.id) {
      console.error("Admin sync createUser error:", createError);
      return new Response(JSON.stringify({ success: false, error: "Unable to create admin auth user" }), {
        status: 500,
        headers: jsonHeaders,
      });
    }
    authUserId = createData.user.id;
  }

  if (!authUserId) {
    return new Response(JSON.stringify({ success: false, error: "Unable to resolve admin auth user" }), {
      status: 500,
      headers: jsonHeaders,
    });
  }

  const { error: roleError } = await supabaseAdmin
    .from("user_roles")
    .insert(
      [{ user_id: authUserId, role: "admin" }],
      { onConflict: ["user_id", "role"], returning: "minimal" }
    );

  if (roleError) {
    console.error("Admin sync user_roles error:", roleError);
    return new Response(JSON.stringify({ success: false, error: "Unable to assign admin role" }), {
      status: 500,
      headers: jsonHeaders,
    });
  }

  return new Response(JSON.stringify({ success: true }), {
    status: 200,
    headers: jsonHeaders,
  });
}

export default {
  async fetch(request: Request, env: unknown, ctx: unknown) {
    try {
      const url = new URL(request.url);
      if (url.pathname === "/api/admin/sync-auth") {
        return await handleAdminSyncAuth(request);
      }

      const handler = await getServerEntry();
      const response = await handler.fetch(request, env, ctx);
      return await normalizeCatastrophicSsrResponse(response);
    } catch (error) {
      console.error(error);
      return brandedErrorResponse();
    }
  },
};
