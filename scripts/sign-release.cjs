#!/usr/bin/env node
const { readFileSync, writeFileSync, existsSync, readdirSync, statSync } = require("fs");
const { createHash, sign, createPrivateKey } = require("crypto");
const { join, relative } = require("path");

const WORKSPACE = process.env["REPL_HOME"] || "/home/runner/workspace";
const PLUGIN_DIR = join(WORKSPACE, "tablemaster-pro");
const OUTPUT_FILE = join(WORKSPACE, "release-meta.json");

function getAllFiles(dir, base) {
  base = base || dir;
  let results = [];
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    const st = statSync(full);
    if (st.isDirectory()) {
      results = results.concat(getAllFiles(full, base));
    } else {
      results.push(relative(base, full));
    }
  }
  return results.sort();
}

function computeContentHash(dir) {
  const files = getAllFiles(dir);
  const hasher = createHash("sha256");
  for (const f of files) {
    hasher.update(f);
    hasher.update(readFileSync(join(dir, f)));
  }
  return hasher.digest("hex");
}

function signData(dataStr, privKeyHex) {
  const seed = Buffer.from(privKeyHex, "hex");
  const pkcs8Prefix = Buffer.from("302e020100300506032b657004220420", "hex");
  const pkcs8 = Buffer.concat([pkcs8Prefix, seed]);
  const privKey = createPrivateKey({ key: pkcs8, format: "der", type: "pkcs8" });
  return sign(null, Buffer.from(dataStr, "utf-8"), privKey).toString("hex");
}

const signingKey = process.env["TMP_SIGNING_KEY"];
if (!signingKey || signingKey.length !== 64) {
  console.error("ERROR: TMP_SIGNING_KEY environment variable not set or invalid");
  process.exit(1);
}

if (!existsSync(PLUGIN_DIR)) {
  console.error("ERROR: Plugin directory not found:", PLUGIN_DIR);
  process.exit(1);
}

const contentHash = computeContentHash(PLUGIN_DIR);
const signature = signData(contentHash, signingKey);

const versionMatch = readFileSync(join(PLUGIN_DIR, "tablemaster-pro.php"), "utf-8").match(/Version:\s*([\d.]+)/);
const version = versionMatch ? versionMatch[1] : "0.0.0";

const meta = {
  version,
  content_hash: contentHash,
  signature,
  signed_at: new Date().toISOString(),
  files_count: getAllFiles(PLUGIN_DIR).length,
};

writeFileSync(OUTPUT_FILE, JSON.stringify(meta, null, 2));
console.log("Release metadata written to", OUTPUT_FILE);
console.log("  Version:", meta.version);
console.log("  Content hash:", meta.content_hash);
console.log("  Signature:", meta.signature.substring(0, 32) + "...");
console.log("  Files:", meta.files_count);
