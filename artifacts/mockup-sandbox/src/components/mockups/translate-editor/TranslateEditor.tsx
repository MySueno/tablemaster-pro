import { useState, useEffect, useRef } from "react";
import "./_group.css";

const sampleData = {
  tableName: "Prijslijst Diensten",
  sourceLang: "Nederlands",
  targetLang: "Engels",
  columns: ["Dienst", "Omschrijving", "Prijs", "Opmerking"],
  rows: [
    {
      section: "TABEL",
      items: [
        { label: "Naam", name: "table_name", original: "Prijslijst Diensten", translated: "Price List Services", prefilled: false },
        { label: "Onderschrift", name: "caption", original: "Alle prijzen zijn exclusief BTW", translated: "All prices are exclusive of VAT", prefilled: false },
      ],
    },
    {
      section: "KOLOMNAMEN",
      items: [
        { label: "Dienst", name: "col_1_label", original: "Dienst", translated: "Service", prefilled: false },
        { label: "Omschrijving", name: "col_2_label", original: "Omschrijving", translated: "Description", prefilled: false },
        { label: "Prijs", name: "col_3_label", original: "Prijs", translated: "Price", prefilled: false },
        { label: "Opmerking", name: "col_4_label", original: "Opmerking", translated: "", prefilled: false },
      ],
    },
    {
      section: "CELINHOUD",
      items: [
        { label: "Dienst", badge: "Header", name: "row_1_col_1", original: "Webdesign", translated: "Web Design", prefilled: false },
        { label: "Omschrijving", badge: "Header", name: "row_1_col_2", original: "Volledig responsive website op maat", translated: "Fully responsive custom website", prefilled: false },
        { label: "Prijs", badge: "Header", name: "row_1_col_3", original: "Vanaf €1.500", translated: "From €1,500", prefilled: false },
        { label: "Opmerking", badge: "Header", name: "row_1_col_4", original: "Inclusief 3 revisierondes", translated: "Including 3 revision rounds", prefilled: false },
        { label: "Dienst", badge: "Body", name: "row_2_col_1", original: "SEO Optimalisatie", translated: "SEO Optimization", prefilled: false },
        { label: "Omschrijving", badge: "Body", name: "row_2_col_2", original: "Technische en inhoudelijke SEO", translated: "", prefilled: false },
        { label: "Prijs", badge: "Body", name: "row_2_col_3", original: "Vanaf €1.500", translated: "From €1,500", prefilled: true },
        { label: "Opmerking", badge: "Body", name: "row_2_col_4", original: "Inclusief 3 revisierondes", translated: "Including 3 revision rounds", prefilled: true },
        { label: "Dienst", badge: "Body", name: "row_3_col_1", original: "Hosting & Onderhoud", translated: "", prefilled: false },
        { label: "Omschrijving", badge: "Body", name: "row_3_col_2", original: "Maandelijks onderhoud en updates", translated: "", prefilled: false },
        { label: "Prijs", badge: "Body", name: "row_3_col_3", original: "€49/maand", translated: "", prefilled: false },
        { label: "Opmerking", badge: "Body", name: "row_3_col_4", original: "Inclusief 3 revisierondes", translated: "Including 3 revision rounds", prefilled: true },
        { label: "Dienst", badge: "Footer", name: "row_4_col_1", original: "Totaal", translated: "Total", prefilled: false },
        { label: "Opmerking", badge: "Footer", name: "row_4_col_4", original: "Inclusief 3 revisierondes", translated: "Including 3 revision rounds", prefilled: true },
      ],
    },
  ],
};

function CopyIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
      <path d="M6 2h10a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v10h10V4H6zM2 6v12h12v-2H4V6H2z"/>
    </svg>
  );
}

export function TranslateEditor() {
  const allItems = sampleData.rows.flatMap((s) => s.items);
  const [translations, setTranslations] = useState<Record<string, string>>(() => {
    const init: Record<string, string> = {};
    allItems.forEach((item) => {
      init[item.name] = item.translated;
    });
    return init;
  });
  const [prefilled, setPrefilled] = useState<Record<string, boolean>>(() => {
    const init: Record<string, boolean> = {};
    allItems.forEach((item) => {
      init[item.name] = item.prefilled;
    });
    return init;
  });
  const [isDirty, setIsDirty] = useState(false);
  const [statusMsg, setStatusMsg] = useState("");
  const [statusClass, setStatusClass] = useState("");

  const totalFields = allItems.length;
  const translatedFields = allItems.filter(
    (item) => translations[item.name]?.trim() && !prefilled[item.name]
  ).length;

  const handleChange = (name: string, value: string) => {
    setTranslations((prev) => ({ ...prev, [name]: value }));
    if (prefilled[name]) {
      setPrefilled((prev) => ({ ...prev, [name]: false }));
    }
    setIsDirty(true);
  };

  const handleBlur = (name: string) => {
    const item = allItems.find((i) => i.name === name);
    if (!item) return;
    const val = translations[name]?.trim();
    if (!val) return;

    const updates: Record<string, string> = {};
    const pfUpdates: Record<string, boolean> = {};
    allItems.forEach((other) => {
      if (other.name === name) return;
      if (other.original === item.original && !translations[other.name]?.trim()) {
        updates[other.name] = val;
        pfUpdates[other.name] = true;
      }
    });
    if (Object.keys(updates).length > 0) {
      setTranslations((prev) => ({ ...prev, ...updates }));
      setPrefilled((prev) => ({ ...prev, ...pfUpdates }));
      setIsDirty(true);
    }
  };

  const handleCopy = (name: string, original: string) => {
    setTranslations((prev) => ({ ...prev, [name]: original }));
    setIsDirty(true);
  };

  const handleSave = () => {
    setStatusMsg("Vertalingen opgeslagen!");
    setStatusClass("success");
    setIsDirty(false);
    const pfReset: Record<string, boolean> = {};
    Object.keys(prefilled).forEach((k) => (pfReset[k] = false));
    setPrefilled(pfReset);
    setTimeout(() => setStatusMsg(""), 3000);
  };

  return (
    <div className="tmp-wp-admin">
      <div className="wrap tmp-wrap">
        <h1>
          Vertaling: {sampleData.tableName}
          <a href="#" className="page-title-action" onClick={(e) => e.preventDefault()}>
            &larr; Terug naar bewerken
          </a>
        </h1>

        <div className="tmp-translate-header">
          <div className="tmp-translate-lang tmp-translate-source">
            <span className="tmp-flag">🇳🇱</span>
            <strong>Origineel:</strong> {sampleData.sourceLang}
          </div>
          <div className="tmp-translate-lang-arrow">➔</div>
          <div className="tmp-translate-lang tmp-translate-target">
            <span className="tmp-flag">🇬🇧</span>
            <strong>Vertaling naar het:</strong> {sampleData.targetLang}
          </div>
          <div className="tmp-translate-progress">
            <span className="tmp-translate-progress-count">{translatedFields}</span>
            {" / "}{totalFields}
            <span className="tmp-translate-progress-label"> vertaald</span>
          </div>
        </div>

        <div className="tmp-translate-table-wrap">
          <table className="tmp-translate-table">
            <thead>
              <tr>
                <th className="tmp-translate-context">Veld</th>
                <th className="tmp-translate-original">{sampleData.sourceLang}</th>
                <th className="tmp-translate-translated">{sampleData.targetLang}</th>
              </tr>
            </thead>
            <tbody>
              {sampleData.rows.map((section) => (
                <>
                  <tr className="tmp-translate-section-header" key={`section-${section.section}`}>
                    <td colSpan={3}>
                      <strong>{section.section}</strong>
                    </td>
                  </tr>
                  {section.items.map((item) => {
                    const val = translations[item.name] || "";
                    const isPf = prefilled[item.name];
                    const isDone = val.trim() !== "" && !isPf;
                    let rowClass = "tmp-translate-row";
                    if (isDone) rowClass += " tmp-translate-done";
                    if (isPf && val.trim() !== "") rowClass += " tmp-translate-prefilled";

                    return (
                      <tr className={rowClass} key={item.name}>
                        <td className="tmp-translate-context">
                          {item.badge && (
                            <span className="tmp-translate-row-type">{item.badge}</span>
                          )}
                          {item.label}
                        </td>
                        <td className="tmp-translate-original">
                          <div className="tmp-translate-original-text">{item.original}</div>
                        </td>
                        <td className="tmp-translate-translated">
                          <div className="tmp-translate-field-wrap">
                            <input
                              type="text"
                              className="tmp-translate-input"
                              data-string-name={item.name}
                              data-original={item.original}
                              value={val}
                              placeholder={item.original}
                              onChange={(e) => handleChange(item.name, e.target.value)}
                              onBlur={() => handleBlur(item.name)}
                            />
                            <button
                              type="button"
                              className="tmp-translate-copy-btn"
                              title="Kopieer origineel"
                              onClick={() => handleCopy(item.name, item.original)}
                            >
                              <CopyIcon />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </>
              ))}
            </tbody>
          </table>
        </div>

        <div className="tmp-translate-save-bar">
          <button
            type="button"
            className="button button-primary"
            onClick={handleSave}
          >
            Vertalingen opslaan
          </button>
          <span
            id="tmp-translate-status"
            className={statusClass}
          >
            {statusMsg}
          </span>
        </div>
      </div>
    </div>
  );
}
