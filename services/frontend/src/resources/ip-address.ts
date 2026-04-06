import type { IpAddress } from "../types/IpAddress";
import type { Pagination } from "../types/Pagination";
import axios from "../services/axios";

const endpoint = "/ip-management/ip-addresses";

export const ipAddressResource = {
  endpoint,

  index(params?: Record<string, any>) {
    return axios.get<{ data: IpAddress[] } & Pagination>(endpoint, { params });
  },

  store(data: { ip_address: string; label: string; comment: string }) {
    return axios.post<{ data: IpAddress }>(endpoint, data);
  },

  show(id: number) {
    return axios.get<{ data: IpAddress }>(`${endpoint}/${id}`);
  },

  update(
    id: number,
    data: {
      label: string;
      comment: string;
    },
  ) {
    return axios.put<{ data: IpAddress }>(`${endpoint}/${id}`, data);
  },

  destroy(id: number) {
    return axios.delete(`${endpoint}/${id}`);
  },
};
