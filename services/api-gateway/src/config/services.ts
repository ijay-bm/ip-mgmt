import type { Application } from "express";
import { createProxyMiddleware, type Options } from "http-proxy-middleware";
import { env } from "./env.js";
import type { ProxyErrorResponse, ServiceConfig } from "../types/index.js";

class ServiceProxy {
  private static readonly serviceConfigs: ServiceConfig[] = [
    {
      path: "/api/v1/auth/",
      url: env.AUTH_SERVICE_URL,
      pathRewrite: { "^/": "/api/v1/auth/" },
      name: "auth"
    },
    {
      path: "/api/v1/ip-management/",
      url: env.IP_MANAGEMENT_URL,
      pathRewrite: { "^/": "/api/v1/ip-management/" },
      name: "ip-management"
    }
  ];

  private static createProxyOptions(service: ServiceConfig): Options {
    return {
      target: service.url,
      changeOrigin: true,
      pathRewrite: service.pathRewrite,
      timeout: service.timeout || env.DEFAULT_TIMEOUT,
      on: {
        error: ServiceProxy.handleProxyError,
        proxyReq: ServiceProxy.handleProxyRequest,
        proxyRes: ServiceProxy.handleProxyResponse
      }
    };
  }

  private static handleProxyError(err: Error, req: any, res: any): void {
    console.error(`Proxy error for ${req.path}:`, err);

    const errorResponse: ProxyErrorResponse = {
      message: "Service unavailable",
      status: 503,
      timestamp: new Date().toISOString()
    };

    // 503 for now
    res
      .status(503)
      .setHeader("Content-Type", "application/json")
      .end(JSON.stringify(errorResponse));
  }

  private static handleProxyRequest(proxyReq: any, req: any): void {
    const clientIp = req.socket?.remoteAddress ?? "";
    proxyReq.setHeader("X-Forwarded-For", clientIp);

    console.info(`Proxy request for ${req.path}:`, proxyReq.path);
  }

  private static handleProxyResponse(proxyRes: any, req: any): void {
    console.info(`Proxy response for ${req.path}:`, proxyRes.statusCode);
  }

  public static setupProxy(app: Application): void {
    ServiceProxy.serviceConfigs.forEach((service) => {
      const proxyOptions = ServiceProxy.createProxyOptions(service);
      app.use(service.path, createProxyMiddleware(proxyOptions));
    });
  }
}

export const proxyServices = (app: Application): void => {
  ServiceProxy.setupProxy(app);
};
