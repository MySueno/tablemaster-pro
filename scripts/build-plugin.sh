#!/bin/bash
set -e

cd /home/runner/workspace

echo "╔══════════════════════════════════════════╗"
echo "║  TableMaster Pro — Build & Sign Pipeline ║"
echo "╚══════════════════════════════════════════╝"
echo ""

echo "▶ Stap 1/4: PHP syntax check..."
errors=0
for f in $(find tablemaster-pro -name "*.php" -type f); do
    result=$(php -l "$f" 2>&1)
    if [ $? -ne 0 ]; then
        echo "  ✗ FOUT: $f"
        echo "    $result"
        errors=$((errors+1))
    fi
done

if [ $errors -gt 0 ]; then
    echo ""
    echo "✗ BUILD AFGEBROKEN: $errors PHP syntax fout(en) gevonden!"
    echo "  Fix de fouten hierboven en probeer opnieuw."
    exit 1
fi
echo "  ✓ Alle PHP-bestanden OK"
echo ""

echo "▶ Stap 2/4: Versie uitlezen..."
version=$(grep "Version:" tablemaster-pro/tablemaster-pro.php | head -1 | sed 's/.*Version: *//' | tr -d '[:space:]')
echo "  ✓ Versie: $version"
echo ""

echo "▶ Stap 3/4: ZIP bouwen..."
rm -f tablemaster-pro.zip
cd tablemaster-pro
zip -r ../tablemaster-pro.zip . -x "*.DS_Store" "*.git*" > /dev/null
cd ..
size=$(du -h tablemaster-pro.zip | cut -f1)
echo "  ✓ tablemaster-pro.zip ($size)"
echo ""

echo "▶ Stap 4/4: Release signeren..."
node scripts/sign-release.cjs
echo ""

echo "╔══════════════════════════════════════════╗"
echo "║  ✓ BUILD GESLAAGD — v$version            "
echo "║  Herstart nu de API server               "
echo "╚══════════════════════════════════════════╝"
