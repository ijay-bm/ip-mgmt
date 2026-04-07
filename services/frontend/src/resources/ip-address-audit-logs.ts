import type { AuditLog } from "../types/AuditLog";
import type { Pagination } from "../types/Pagination";
import axios from "../services/axios";

const endpoint = "/ip-management/ip-addresses/audit-logs";

export const ipAddressAuditLogResource = {
  endpoint,

  index(params?: Record<string, any>) {
    return axios.get<{ data: AuditLog[] } & Pagination>(endpoint, { params });
  },
};
