const fs = require('fs');
const path = require('path');

const REPO_OWNER = 'MySueno';
const REPO_NAME = 'tablemaster-pro';
const PLUGIN_DIR = path.join(__dirname, '..', 'tablemaster-pro');
const GITHUB_API = 'https://api.github.com';
const BRANCH = 'main';

const token = process.env.GITHUB_TOKEN || process.argv[2];
if (!token) {
  console.error('✗ GITHUB_TOKEN niet ingesteld');
  console.error('  Gebruik: GITHUB_TOKEN=xxx node scripts/push-to-github.cjs');
  console.error('  Of: node scripts/push-to-github.cjs <token>');
  process.exit(1);
}

async function gh(endpoint, opts = {}) {
  const url = `${GITHUB_API}${endpoint}`;
  const resp = await fetch(url, {
    ...opts,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/vnd.github+json',
      'Content-Type': 'application/json',
      ...(opts.headers || {}),
    },
  });
  if (!resp.ok && resp.status !== 404 && resp.status !== 422) {
    const body = await resp.text();
    throw new Error(`GitHub API ${resp.status}: ${body.substring(0, 300)}`);
  }
  return resp;
}

function collectFiles(dir, prefix = '') {
  const entries = [];
  for (const item of fs.readdirSync(dir, { withFileTypes: true })) {
    if (item.name.startsWith('.') || item.name === 'node_modules') continue;
    const fullPath = path.join(dir, item.name);
    const repoPath = prefix ? `${prefix}/${item.name}` : item.name;
    if (item.isDirectory()) {
      entries.push(...collectFiles(fullPath, repoPath));
    } else {
      entries.push({ path: `tablemaster-pro/${repoPath}`, fullPath });
    }
  }
  return entries;
}

async function pushToGitHub() {
  console.log('▶ GitHub push starten...');

  const version = fs.readFileSync(path.join(PLUGIN_DIR, 'tablemaster-pro.php'), 'utf8')
    .match(/Version:\s*([\d.]+)/)?.[1] || 'unknown';
  console.log(`  ✓ Plugin versie: ${version}`);

  let parentSha = null;
  const refResp = await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/ref/heads/${BRANCH}`);
  if (refResp.ok) {
    const refData = await refResp.json();
    parentSha = refData.object.sha;
    console.log(`  ✓ Bestaande branch gevonden: ${parentSha.substring(0, 7)}`);
  } else {
    console.log('  ℹ Repository is leeg, eerste commit wordt aangemaakt');
  }

  const files = collectFiles(PLUGIN_DIR);
  console.log(`  ✓ ${files.length} plugin-bestanden verzameld`);

  const zipPath = path.join(__dirname, '..', 'tablemaster-pro.zip');
  const allFiles = [...files];
  if (fs.existsSync(zipPath)) {
    allFiles.push({ path: 'tablemaster-pro.zip', fullPath: zipPath });
  }

  console.log('  ▶ Bestanden uploaden naar GitHub...');
  const treeEntries = [];
  let uploaded = 0;
  for (const file of allFiles) {
    const content = fs.readFileSync(file.fullPath);
    const base64 = content.toString('base64');

    const blobResp = await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/blobs`, {
      method: 'POST',
      body: JSON.stringify({ content: base64, encoding: 'base64' }),
    });
    const blobData = await blobResp.json();
    treeEntries.push({
      path: file.path,
      mode: '100644',
      type: 'blob',
      sha: blobData.sha,
    });
    uploaded++;
    if (uploaded % 10 === 0) console.log(`    ${uploaded}/${allFiles.length} bestanden...`);
  }
  console.log(`  ✓ ${treeEntries.length} blobs aangemaakt`);

  const treeResp = await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/trees`, {
    method: 'POST',
    body: JSON.stringify({ tree: treeEntries }),
  });
  const treeData = await treeResp.json();
  console.log(`  ✓ Tree aangemaakt: ${treeData.sha.substring(0, 7)}`);

  const commitBody = {
    message: `TableMaster Pro v${version}`,
    tree: treeData.sha,
  };
  if (parentSha) commitBody.parents = [parentSha];

  const commitResp = await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/commits`, {
    method: 'POST',
    body: JSON.stringify(commitBody),
  });
  const commitData = await commitResp.json();
  console.log(`  ✓ Commit aangemaakt: ${commitData.sha.substring(0, 7)}`);

  if (parentSha) {
    await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/refs/heads/${BRANCH}`, {
      method: 'PATCH',
      body: JSON.stringify({ sha: commitData.sha, force: true }),
    });
  } else {
    await gh(`/repos/${REPO_OWNER}/${REPO_NAME}/git/refs`, {
      method: 'POST',
      body: JSON.stringify({ ref: `refs/heads/${BRANCH}`, sha: commitData.sha }),
    });
  }
  console.log(`  ✓ Branch '${BRANCH}' bijgewerkt`);

  console.log('');
  console.log(`╔══════════════════════════════════════════╗`);
  console.log(`║  ✓ GEPUSHT naar GitHub — v${version}`);
  console.log(`║  https://github.com/${REPO_OWNER}/${REPO_NAME}`);
  console.log(`╚══════════════════════════════════════════╝`);
}

pushToGitHub().catch(err => {
  console.error('✗ GitHub push mislukt:', err.message);
  process.exit(1);
});
