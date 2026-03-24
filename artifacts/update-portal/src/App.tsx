import { Switch, Route, Router as WouterRouter } from "wouter";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import NotFound from "@/pages/not-found";
import Preview from "@/pages/preview";

const queryClient = new QueryClient();

function Home() {
  return (
    <div className="min-h-screen w-full flex items-center justify-center" style={{ background: "#f8faf8" }}>
      <div className="text-center max-w-md mx-auto p-8">
        <div style={{ fontSize: "3rem", marginBottom: "1rem" }}>&#128221;</div>
        <h1 style={{ fontSize: "1.5rem", fontWeight: 700, color: "#2e7d32", marginBottom: "0.5rem" }}>
          TableMaster Pro Update Server
        </h1>
        <p style={{ color: "#666", marginBottom: "1.5rem" }}>
          Deze server levert automatische updates voor de TableMaster Pro WordPress plugin.
        </p>
        <div style={{ background: "#fff", border: "1px solid #e0e0e0", borderRadius: "8px", padding: "1rem", textAlign: "left" }}>
          <p style={{ fontSize: "0.85rem", color: "#444", marginBottom: "0.5rem" }}>
            <strong>API Endpoints:</strong>
          </p>
          <ul style={{ fontSize: "0.8rem", color: "#666", listStyle: "none", padding: 0 }}>
            <li style={{ marginBottom: "4px" }}>/api/wp-update/info</li>
            <li style={{ marginBottom: "4px" }}>/api/wp-update/version</li>
            <li>/api/wp-update/download</li>
          </ul>
        </div>
        <a href={import.meta.env.BASE_URL + "preview"} style={{ display: "inline-block", marginTop: "1rem", padding: "8px 20px", background: "#2e7d32", color: "#fff", borderRadius: 8, textDecoration: "none", fontWeight: 600, fontSize: "0.9rem" }}>
          Live Preview
        </a>
      </div>
    </div>
  );
}

function Router() {
  return (
    <Switch>
      <Route path="/" component={Home} />
      <Route path="/preview" component={Preview} />
      <Route component={NotFound} />
    </Switch>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <WouterRouter base={import.meta.env.BASE_URL.replace(/\/$/, "")}>
          <Router />
        </WouterRouter>
        <Toaster />
      </TooltipProvider>
    </QueryClientProvider>
  );
}

export default App;
