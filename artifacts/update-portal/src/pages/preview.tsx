import { useEffect, useRef, useState } from "react";

const FRONTEND_CSS = `/tablemaster-assets/frontend.css`;

const DEMO_COLUMNS = [
  { id: "1", label: "Behandeling", type: "text", sortable: true, filterable: false },
  { id: "2", label: "Categorie", type: "text", sortable: true, filterable: true },
  { id: "3", label: "Indicatie", type: "text", sortable: false, filterable: false },
  { id: "4", label: "Arts", type: "text", sortable: true, filterable: false },
  { id: "5", label: "Locatie", type: "text", sortable: true, filterable: false },
  { id: "6", label: "Duur (min)", type: "number", sortable: true, filterable: false },
  { id: "7", label: "Prijs (€)", type: "number", sortable: true, filterable: false },
  { id: "8", label: "Vergoeding", type: "text", sortable: false, filterable: false },
];

const DEMO_ROWS = [
  { id: "r1", type: "group_1", parent: null, cells: { "1": "Massage & Fysiotherapie" } },
  { id: "r2", type: "group_2", parent: "r1", cells: { "1": "Klassieke Massage" } },
  { id: "r3", type: "data", parent: "r2", cells: { "1": "Rugmassage 30 min", "2": "Fysiotherapie", "3": "Spierspanning", "4": "Dr. Jansen", "5": "Amsterdam", "6": "30", "7": "45", "8": "Gedeeltelijk" } },
  { id: "r4", type: "data", parent: "r2", cells: { "1": "Rugmassage 60 min", "2": "Fysiotherapie", "3": "Spierspanning", "4": "Dr. Jansen", "5": "Amsterdam", "6": "60", "7": "75", "8": "Gedeeltelijk" } },
  { id: "r5", type: "data", parent: "r2", cells: { "1": "Nekmassage 30 min", "2": "Fysiotherapie", "3": "Nekpijn", "4": "Dr. De Vries", "5": "Rotterdam", "6": "30", "7": "50", "8": "Nee" } },
  { id: "r6", type: "group_2", parent: "r1", cells: { "1": "Sportmassage" } },
  { id: "r7", type: "data", parent: "r6", cells: { "1": "Sportmassage 45 min", "2": "Fysiotherapie", "3": "Sportblessure", "4": "Dr. Bakker", "5": "Utrecht", "6": "45", "7": "60", "8": "Nee" } },
  { id: "r8", type: "data", parent: "r6", cells: { "1": "Sportmassage 90 min", "2": "Fysiotherapie", "3": "Sportblessure", "4": "Dr. Bakker", "5": "Utrecht", "6": "90", "7": "110", "8": "Nee" } },
  { id: "r9", type: "group_1", parent: null, cells: { "1": "Injecties & Cosmetisch" } },
  { id: "r10", type: "group_2", parent: "r9", cells: { "1": "Botox" } },
  { id: "r11", type: "group_3", parent: "r10", cells: { "1": "Voorhoofd Botox" } },
  { id: "r12", type: "data", parent: "r11", cells: { "1": "Botox 1 zone", "2": "Cosmetisch", "3": "Rimpels", "4": "Dr. Smit", "5": "Den Haag", "6": "15", "7": "150", "8": "Nee" } },
  { id: "r13", type: "data", parent: "r11", cells: { "1": "Botox 3 zones", "2": "Cosmetisch", "3": "Rimpels", "4": "Dr. Smit", "5": "Den Haag", "6": "30", "7": "350", "8": "Nee" } },
  { id: "r14", type: "data", parent: "r11", cells: { "1": "Botox full face", "2": "Cosmetisch", "3": "Rimpels", "4": "Dr. Smit", "5": "Den Haag", "6": "45", "7": "500", "8": "Nee" } },
  { id: "r15", type: "group_1", parent: null, cells: { "1": "Dermatologie" } },
  { id: "r16", type: "data", parent: "r15", cells: { "1": "Curettage wrat", "2": "Dermatologie", "3": "Wratten", "4": "Dr. Visser", "5": "Eindhoven", "6": "20", "7": "80", "8": "Ja" } },
  { id: "r17", type: "data", parent: "r15", cells: { "1": "Curettage fibroom", "2": "Dermatologie", "3": "Huidafwijking", "4": "Dr. Visser", "5": "Eindhoven", "6": "25", "7": "95", "8": "Ja" } },
  { id: "r18", type: "data", parent: "r15", cells: { "1": "Moedervlek controle", "2": "Dermatologie", "3": "Screening", "4": "Dr. Visser", "5": "Eindhoven", "6": "15", "7": "65", "8": "Ja" } },
];

function generateSmallTable() {
  const cols = [
    { id: "s1", label: "Product", type: "text", sortable: true },
    { id: "s2", label: "Prijs", type: "number", sortable: true },
    { id: "s3", label: "Voorraad", type: "number", sortable: true },
  ];
  const rows = [
    { id: "sm1", type: "data" as const, parent: null, cells: { s1: "Widget A", s2: "€ 12,50", s3: "340" } },
    { id: "sm2", type: "data" as const, parent: null, cells: { s1: "Widget B", s2: "€ 29,95", s3: "82" } },
    { id: "sm3", type: "data" as const, parent: null, cells: { s1: "Widget C", s2: "€ 7,00", s3: "1.205" } },
  ];
  return { cols, rows };
}

function generateLargeTable() {
  const cols = Array.from({ length: 25 }, (_, i) => ({
    id: `L${i + 1}`,
    label: `Kolom ${i + 1}`,
    type: i < 5 ? "text" : "number",
    sortable: true,
  }));

  const sampleWords = ["Alpha", "Beta", "Gamma", "Delta", "Epsilon", "Zeta", "Eta", "Theta", "Iota", "Kappa"];
  const rows = Array.from({ length: 25 }, (_, r) => {
    const cells: Record<string, string> = {};
    cols.forEach((col, c) => {
      if (c < 5) {
        cells[col.id] = `${sampleWords[r % sampleWords.length]}-${c + 1}`;
      } else {
        cells[col.id] = `${Math.floor(Math.random() * 9000 + 1000)}`;
      }
    });
    return { id: `lg${r + 1}`, type: "data" as const, parent: null, cells };
  });
  return { cols, rows };
}

const SMALL = generateSmallTable();
const LARGE = generateLargeTable();

const THEMES = {
  red: {
    header_bg: "#D32637", header_text: "#ffffff",
    group1_bg: "#D32637", group1_text: "#ffffff",
    group2_bg: "#F9E6E7", group2_text: "#D32637",
    group3_bg: "#ffffff", group3_text: "#1a1a1a",
    odd_bg: "#F8F8F8", even_bg: "#ffffff",
    hover_bg: "#fce4e4", border_color: "#e8e8e8",
    accent_color: "#D32637",
  },
  green: {
    header_bg: "#2e7d32", header_text: "#ffffff",
    group1_bg: "#4caf50", group1_text: "#ffffff",
    group2_bg: "#81c784", group2_text: "#1a1a1a",
    group3_bg: "#c8e6c9", group3_text: "#1a1a1a",
    odd_bg: "#ffffff", even_bg: "#f1f8e9",
    hover_bg: "#dcedc8", border_color: "#a5d6a7",
    accent_color: "#2e7d32",
  },
  blue: {
    header_bg: "#1565c0", header_text: "#ffffff",
    group1_bg: "#1976d2", group1_text: "#ffffff",
    group2_bg: "#90caf9", group2_text: "#1a1a1a",
    group3_bg: "#e3f2fd", group3_text: "#1a1a1a",
    odd_bg: "#ffffff", even_bg: "#e8f4fd",
    hover_bg: "#bbdefb", border_color: "#90caf9",
    accent_color: "#1565c0",
  },
  grey: {
    header_bg: "#424242", header_text: "#ffffff",
    group1_bg: "#616161", group1_text: "#ffffff",
    group2_bg: "#bdbdbd", group2_text: "#1a1a1a",
    group3_bg: "#eeeeee", group3_text: "#1a1a1a",
    odd_bg: "#ffffff", even_bg: "#f5f5f5",
    hover_bg: "#e0e0e0", border_color: "#bdbdbd",
    accent_color: "#424242",
  },
};

type ThemeKey = keyof typeof THEMES;

interface TableColumn {
  id: string;
  label: string;
  type: string;
  sortable: boolean;
}
interface TableRow {
  id: string;
  type: string;
  parent: string | null;
  cells: Record<string, string>;
}

function DemoTable({
  title,
  columns,
  rows,
  colors,
  uid,
}: {
  title: string;
  columns: TableColumn[];
  rows: TableRow[];
  colors: typeof THEMES.red;
  uid: string;
}) {
  const cssVars: Record<string, string> = {
    "--tmp-header-bg": colors.header_bg,
    "--tmp-header-text": colors.header_text,
    "--tmp-group1-bg": colors.group1_bg,
    "--tmp-group1-text": colors.group1_text,
    "--tmp-group2-bg": colors.group2_bg,
    "--tmp-group2-text": colors.group2_text,
    "--tmp-group3-bg": colors.group3_bg,
    "--tmp-group3-text": colors.group3_text,
    "--tmp-odd-bg": colors.odd_bg,
    "--tmp-even-bg": colors.even_bg,
    "--tmp-hover-bg": colors.hover_bg,
    "--tmp-border": colors.border_color,
    "--tmp-accent": colors.accent_color,
    "--tmp-radius": "12px",
  };

  let dataIdx = 0;

  function renderRow(row: TableRow) {
    const isGroup = row.type !== "data";
    const indentLvl = row.type === "group_2" ? 1 : row.type === "group_3" ? 2 : 0;

    if (isGroup) {
      const label = row.cells[columns[0]?.id] || "";
      return (
        <tr key={row.id} className={`tmp-row tmp-type-${row.type}`} data-row-id={row.id} data-row-type={row.type} data-parent-id={row.parent || ""}>
          <td className="tmp-td tmp-group-cell" colSpan={columns.length} style={{ paddingLeft: `${indentLvl * 24 + 12}px` }}>
            <div className="tmp-group-cell-inner">
              <span className="tmp-group-label">{label}</span>
            </div>
          </td>
        </tr>
      );
    }

    const zebraClass = dataIdx % 2 === 0 ? "tmp-odd" : "tmp-even";
    dataIdx++;

    return (
      <tr key={row.id} className={`tmp-row tmp-type-data ${zebraClass}`} data-row-id={row.id} data-row-type="data" data-parent-id={row.parent || ""}>
        {columns.map((col) => (
          <td key={col.id} className="tmp-td" data-col-id={col.id} data-label={col.label}>
            {row.cells[col.id] || ""}
          </td>
        ))}
      </tr>
    );
  }

  const dataRowCount = rows.filter((r) => r.type === "data").length;

  return (
    <div id={uid} className="tmp-wrapper tmp-mobile-scroll" style={cssVars as React.CSSProperties}
      data-table-id={uid} data-per-page="-1" data-collapsible="0" data-mobile-mode="scroll">

      <div className="tmp-caption" style={{ marginBottom: 8 }}>{title}</div>

      <div className="tmp-table-scroll-wrapper">
        <table className="tmp-table" role="grid" aria-label={title}
          style={{ minWidth: `${Math.max(400, columns.length * 120)}px` }}>
          <thead>
            <tr className="tmp-header-row">
              {columns.map((col) => (
                <th key={col.id} className={`tmp-th${col.sortable ? " tmp-sortable" : ""}`}
                  data-col-id={col.id} data-col-type={col.type}
                  style={{ textAlign: "left" }}>
                  {col.label}
                  {col.sortable && <span className="tmp-sort-icon" aria-hidden="true" />}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="tmp-tbody">
            {rows.map((row) => renderRow(row))}
          </tbody>
        </table>
      </div>

      <div className="tmp-controls tmp-controls-bottom">
        <div className="tmp-info-text">{dataRowCount} resultaten — {columns.length} kolommen</div>
      </div>
    </div>
  );
}

export default function Preview() {
  const [theme, setTheme] = useState<ThemeKey>("red");
  const [viewportWidth, setViewportWidth] = useState<number | null>(null);
  const colors = THEMES[theme];

  useEffect(() => {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = import.meta.env.BASE_URL + "tablemaster-assets/frontend.css";
    document.head.appendChild(link);
    return () => { document.head.removeChild(link); };
  }, []);

  return (
    <div style={{ minHeight: "100vh", background: "#f5f5f5", fontFamily: "system-ui, sans-serif" }}>
      <div style={{ background: "#fff", borderBottom: "1px solid #e0e0e0", padding: "16px 24px", position: "sticky", top: 0, zIndex: 100 }}>
        <div style={{ maxWidth: 1200, margin: "0 auto", display: "flex", alignItems: "center", gap: 24, flexWrap: "wrap" }}>
          <h1 style={{ fontSize: "1.2rem", fontWeight: 700, margin: 0, color: "#333" }}>
            TableMaster Pro — Live Preview
          </h1>

          <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
            <label style={{ fontSize: "0.85rem", color: "#666" }}>Thema:</label>
            {(Object.keys(THEMES) as ThemeKey[]).map((t) => (
              <button key={t} onClick={() => setTheme(t)}
                style={{
                  padding: "4px 12px", borderRadius: 6, border: theme === t ? `2px solid ${THEMES[t].header_bg}` : "1px solid #ccc",
                  background: theme === t ? THEMES[t].header_bg : "#fff", color: theme === t ? "#fff" : "#333",
                  cursor: "pointer", fontSize: "0.8rem", fontWeight: 600, textTransform: "capitalize",
                }}>
                {t}
              </button>
            ))}
          </div>

          <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
            <label style={{ fontSize: "0.85rem", color: "#666" }}>Breedte:</label>
            {[
              { label: "Desktop", w: null },
              { label: "Tablet", w: 768 },
              { label: "Mobiel", w: 375 },
              { label: "Smal", w: 320 },
            ].map((v) => (
              <button key={v.label} onClick={() => setViewportWidth(v.w)}
                style={{
                  padding: "4px 12px", borderRadius: 6,
                  border: viewportWidth === v.w ? "2px solid #333" : "1px solid #ccc",
                  background: viewportWidth === v.w ? "#333" : "#fff",
                  color: viewportWidth === v.w ? "#fff" : "#333",
                  cursor: "pointer", fontSize: "0.8rem",
                }}>
                {v.label}{v.w ? ` (${v.w}px)` : ""}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div style={{ padding: "32px 24px" }}>
        <div style={{
          maxWidth: viewportWidth ? `${viewportWidth}px` : "1200px",
          margin: "0 auto",
          transition: "max-width 0.3s ease",
        }}>
          {viewportWidth && (
            <div style={{ marginBottom: 12, padding: "6px 12px", background: "#e0e0e0", borderRadius: 6, fontSize: "0.75rem", color: "#666", textAlign: "center" }}>
              Simulatie: {viewportWidth}px breed
            </div>
          )}

          <div style={{ background: "#fff", borderRadius: 12, padding: 24, boxShadow: "0 2px 12px rgba(0,0,0,0.08)", marginBottom: 32 }}>
            <h2 style={{ fontSize: "1rem", fontWeight: 600, color: "#999", margin: "0 0 16px 0", textTransform: "uppercase", letterSpacing: 1 }}>
              Kleine tabel — 3 kolommen, 3 rijen
            </h2>
            <DemoTable title="Productvoorraad" columns={SMALL.cols} rows={SMALL.rows} colors={colors} uid="tmp-small" />
          </div>

          <div style={{ background: "#fff", borderRadius: 12, padding: 24, boxShadow: "0 2px 12px rgba(0,0,0,0.08)", marginBottom: 32 }}>
            <h2 style={{ fontSize: "1rem", fontWeight: 600, color: "#999", margin: "0 0 16px 0", textTransform: "uppercase", letterSpacing: 1 }}>
              Medische demo — 8 kolommen, 18 rijen (met groepering)
            </h2>
            <DemoTable title="Medische Behandelingen — Overzicht" columns={DEMO_COLUMNS} rows={DEMO_ROWS} colors={colors} uid="tmp-medical" />
          </div>

          <div style={{ background: "#fff", borderRadius: 12, padding: 24, boxShadow: "0 2px 12px rgba(0,0,0,0.08)", marginBottom: 32 }}>
            <h2 style={{ fontSize: "1rem", fontWeight: 600, color: "#999", margin: "0 0 16px 0", textTransform: "uppercase", letterSpacing: 1 }}>
              Grote tabel — 25 kolommen, 25 rijen
            </h2>
            <DemoTable title="Dataset 25×25" columns={LARGE.cols} rows={LARGE.rows} colors={colors} uid="tmp-large" />
          </div>
        </div>
      </div>
    </div>
  );
}
