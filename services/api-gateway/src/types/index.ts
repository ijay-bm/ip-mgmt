export interface Env {
  SERVICE_NAME: string;
  PORT: number;
  DEFAULT_TIMEOUT: number;
  AUTH_SERVICE_URL: string;
  IP_MANAGEMENT_URL: string;
}

export interface ServiceConfig {
  path: string;
  url: string;
  pathRewrite: Record<string, string>;
  name: string;
  timeout?: number;
}

export interface ProxyErrorResponse {
  message: string;
  status: number;
  timestamp: string;
}
