import type { AuditLog } from "../types/AuditLog";
import type { Pagination } from "../types/Pagination";
import axios from "../services/axios";

const endpoint = "/auth/users/audit-logs";

export const userAuditLogResource = {
  endpoint,

  index(params?: Record<string, any>) {
    return axios.get<{ data: AuditLog[] } & Pagination>(endpoint, { params });
  },
};
