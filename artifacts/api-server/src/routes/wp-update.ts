import { Router, type IRouter, type Request, type Response } from "express";
import { readFileSync, existsSync, statSync } from "fs";
import { join } from "path";

const router: IRouter = Router();

const PLUGIN_SLUG = "tablemaster-pro";
const PLUGIN_FILE = "tablemaster-pro/tablemaster-pro.php";
const WORKSPACE = process.env["REPL_HOME"] || "/home/runner/workspace";

function getPluginVersion(): string {
  const mainPhp = join(WORKSPACE, PLUGIN_SLUG, "tablemaster-pro.php");
  if (!existsSync(mainPhp)) return "1.0.0";
  const content = readFileSync(mainPhp, "utf-8");
  const match = content.match(/Version:\s*([\d.]+)/);
  return match ? match[1] : "1.0.0";
}

function getZipPath(): string {
  return join(WORKSPACE, `${PLUGIN_SLUG}.zip`);
}

function getBaseUrl(req: Request): string {
  const proto = req.headers["x-forwarded-proto"] || req.protocol || "https";
  const host = req.headers["x-forwarded-host"] || req.headers["host"] || "localhost";
  return `${proto}://${host}`;
}

router.get("/wp-update/info", (req: Request, res: Response) => {
  const version = getPluginVersion();
  const baseUrl = getBaseUrl(req);
  const zipPath = getZipPath();
  const zipExists = existsSync(zipPath);

  const response = {
    name: "TableMaster Pro",
    slug: PLUGIN_SLUG,
    version: version,
    author: "TableMaster Pro",
    author_profile: "https://example.com",
    requires: "5.8",
    tested: "6.7",
    requires_php: "7.4",
    download_url: zipExists ? `${baseUrl}/api/wp-update/download` : "",
    sections: {
      description:
        "Maak krachtige, interactieve tabellen met groepering, sortering, filtering en paginering.",
      changelog: `<h4>${version}</h4><ul><li>Verbeterde layout voor groepsrijen (volle-breedte gekleurde balken)</li><li>Verbeterde mobiele kaartmodus</li><li>Automatische updates via update-server</li></ul>`,
    },
    banners: {
      low: "",
      high: "",
    },
    last_updated: new Date().toISOString().split("T")[0],
  };

  res.json(response);
});

router.get("/wp-update/version", (req: Request, res: Response) => {
  const version = getPluginVersion();
  const baseUrl = getBaseUrl(req);

  res.json({
    version,
    slug: PLUGIN_SLUG,
    plugin: PLUGIN_FILE,
    download_url: `${baseUrl}/api/wp-update/download`,
  });
});

router.get("/wp-update/download", (_req: Request, res: Response) => {
  const zipPath = getZipPath();

  if (!existsSync(zipPath)) {
    res.status(404).json({ error: "Plugin ZIP not found" });
    return;
  }

  const stat = statSync(zipPath);
  res.setHeader("Content-Type", "application/zip");
  res.setHeader(
    "Content-Disposition",
    `attachment; filename="${PLUGIN_SLUG}.zip"`,
  );
  res.setHeader("Content-Length", stat.size);

  const data = readFileSync(zipPath);
  res.send(data);
});

export default router;
