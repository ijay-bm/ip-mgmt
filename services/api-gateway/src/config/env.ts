import type { Env } from "../types/index.js";
import pkg from "../../package.json" with { type: "json" };

export const env: Env = {
  SERVICE_NAME: pkg.name,
  PORT: Number(process.env.PORT) || 3000,
  DEFAULT_TIMEOUT: Number(process.env.DEFAULT_TIMEOUT || "30000"),
  AUTH_SERVICE_URL: process.env.AUTH_SERVICE_URL || "http://localhost:8000",
  IP_MANAGEMENT_URL: process.env.IP_MANAGEMENT_URL || "http://localhost:8001"
};
