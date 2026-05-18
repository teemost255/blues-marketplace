export function AnimatedBlobs({ withOrbits = false }: { withOrbits?: boolean }) {
  return (
    <div className="bg-blobs" aria-hidden>
      <span />
      {withOrbits && (
        <>
          <div className="ring-orbit" style={{ width: 600, height: 600, top: "10%", left: "10%" }} />
          <div className="ring-orbit" style={{ width: 900, height: 900, top: "-10%", left: "30%", animationDuration: "60s" }} />
          <div className="ring-orbit" style={{ width: 400, height: 400, bottom: "5%", right: "10%", animationDuration: "30s" }} />
        </>
      )}
    </div>
  );
}
