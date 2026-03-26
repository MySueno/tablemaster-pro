import { Router, type IRouter, type Request, type Response } from "express";
import { readFileSync, existsSync, statSync, createReadStream } from "fs";
import { join } from "path";

const router: IRouter = Router();

const PLUGIN_SLUG = "tablemaster-pro";
const PLUGIN_FILE = "tablemaster-pro/tablemaster-pro.php";
const WORKSPACE = process.env["REPL_HOME"] || "/home/runner/workspace";
const VALID_LICENSE_KEYS = ["mysueno"];

function validateLicenseKey(req: Request): boolean {
  const key = (req.headers["x-license-key"] as string) || (req.query["license_key"] as string) || "";
  return VALID_LICENSE_KEYS.includes(key.trim().toLowerCase());
}

function getPublicDomain(): string {
  if (process.env["PLUGIN_BASE_URL"]) {
    return process.env["PLUGIN_BASE_URL"].replace(/\/+$/, "");
  }
  if (process.env["REPLIT_DEPLOYMENT"] === "1" && process.env["REPLIT_DOMAINS"]) {
    return `https://${process.env["REPLIT_DOMAINS"].split(",")[0]}`;
  }
  if (process.env["REPLIT_DEV_DOMAIN"]) {
    return `https://${process.env["REPLIT_DEV_DOMAIN"]}`;
  }
  return `https://${process.env["REPL_SLUG"] || "localhost"}.replit.app`;
}

function getBaseUrl(): string {
  return getPublicDomain();
}

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

interface ReleaseMeta {
  version: string;
  content_hash: string;
  signature: string;
  signed_at: string;
  files_count: number;
}

function getReleaseMeta(): ReleaseMeta | null {
  const metaPath = join(WORKSPACE, "release-meta.json");
  if (!existsSync(metaPath)) return null;
  try {
    return JSON.parse(readFileSync(metaPath, "utf-8"));
  } catch {
    return null;
  }
}

function escapeHtml(str: string): string {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function getChangelog(currentVersion: string): string {
  const readmePath = join(WORKSPACE, PLUGIN_SLUG, "readme.txt");
  if (!existsSync(readmePath)) {
    return `<h4>${escapeHtml(currentVersion)}</h4><ul><li>Update beschikbaar</li></ul>`;
  }
  const content = readFileSync(readmePath, "utf-8");
  const changelogMatch = content.match(
    /== Changelog ==\s*([\s\S]*?)(?:==\s|$)/,
  );
  if (!changelogMatch) {
    return `<h4>${escapeHtml(currentVersion)}</h4><ul><li>Update beschikbaar</li></ul>`;
  }
  const raw = changelogMatch[1].trim();
  let html = "";
  for (const line of raw.split("\n")) {
    const trimmed = line.trim();
    if (trimmed.startsWith("= ") && trimmed.endsWith(" =")) {
      if (html) html += "</ul>";
      html += `<h4>${escapeHtml(trimmed.slice(2, -2))}</h4><ul>`;
    } else if (trimmed.startsWith("* ")) {
      html += `<li>${escapeHtml(trimmed.slice(2))}</li>`;
    }
  }
  if (html) html += "</ul>";
  return (
    html ||
    `<h4>${escapeHtml(currentVersion)}</h4><ul><li>Update beschikbaar</li></ul>`
  );
}

router.get("/wp-update/info", (_req: Request, res: Response) => {
  if (!validateLicenseKey(_req)) {
    res.status(403).json({ error: "Ongeldige licentiecode" });
    return;
  }
  const version = getPluginVersion();
  const baseUrl = getBaseUrl();
  const zipPath = getZipPath();
  const zipExists = existsSync(zipPath);

  const releaseMeta = getReleaseMeta();
  const contentHash = releaseMeta?.content_hash || "";
  const signature = releaseMeta?.signature || "";

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
    content_hash: contentHash,
    signature: signature,
    sections: {
      description:
        "Maak krachtige, interactieve tabellen met groepering, sortering, filtering en paginering.",
      changelog: getChangelog(version),
    },
    banners: {
      low: "",
      high: "",
    },
    last_updated: new Date().toISOString().split("T")[0],
  };

  res.json(response);
});

router.get("/wp-update/version", (_req: Request, res: Response) => {
  const version = getPluginVersion();
  const baseUrl = getBaseUrl();

  res.json({
    version,
    slug: PLUGIN_SLUG,
    plugin: PLUGIN_FILE,
    download_url: `${baseUrl}/api/wp-update/download`,
  });
});

router.get("/wp-update/download", (_req: Request, res: Response) => {
  if (!validateLicenseKey(_req)) {
    res.status(403).json({ error: "Ongeldige licentiecode" });
    return;
  }

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

  const stream = createReadStream(zipPath);
  stream.pipe(res);
  stream.on("error", () => {
    if (!res.headersSent) {
      res.status(500).json({ error: "Error reading file" });
    }
  });
});

export default router;
