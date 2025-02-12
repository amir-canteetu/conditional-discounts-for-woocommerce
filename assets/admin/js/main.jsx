import { createRoot } from "react-dom/client";
import RuleBuilder from "@/RuleBuilder";

document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("cdwc-rule-builder-root");
  if (container) {
    createRoot(container).render(<RuleBuilder />);
  }
});
