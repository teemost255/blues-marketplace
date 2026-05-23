import { supabase } from "../supabase/client";

type SignInOptions = {
  redirect_uri?: string;
  extraParams?: Record<string, string>;
};

export const lovable = {
  auth: {
    signInWithOAuth: async (provider: "google" | "apple" | "microsoft" | "lovable", opts?: SignInOptions) => {
      const { data, error } = await supabase.auth.signInWithOAuth({
        provider: provider === "lovable" ? "google" : provider,
        options: {
          redirectTo: opts?.redirect_uri ?? (typeof window !== "undefined" ? window.location.origin : "/"),
          queryParams: opts?.extraParams,
        },
      });

      if (error) {
        return { error, redirected: false };
      }

      if (data?.url) {
        if (typeof window !== "undefined") window.location.href = data.url;
        return { redirected: true };
      }

      return { redirected: false };
    },
  },
};
