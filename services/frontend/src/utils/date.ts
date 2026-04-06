import { format } from "date-fns";

const DEFAULT_FORMAT = "MMM dd, yyyy hh:mm a";

export function formatDate(
  iso: string | Date | null,
  dateFormat = DEFAULT_FORMAT,
): string {
  if (!iso) return "—";
  const date = typeof iso === "string" ? new Date(iso) : iso;
  if (Number.isNaN(date.getTime())) return "—";
  try {
    return format(date, dateFormat);
  } catch {
    return "—";
  }
}
