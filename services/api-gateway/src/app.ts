import dotenv from "dotenv";
dotenv.config();

import express from "express";
import helmet from "helmet";
import cors from "cors";
import type { NextFunction, Request, Response } from "express";
import { env } from "./config/env.js";
import { proxyServices } from "./config/services.js";

const app = express();

app.use(helmet());
app.use(cors());
app.use(express.json());

app.get("/health", (req: Request, res: Response) => {
  res.status(200).send({
    status: "OK"
  });
});

// Service Routes
proxyServices(app);

// 404 Handler
app.use((req: Request, res: Response) => {
  res.status(404).json({ message: "Resource Not Found" });
});

// Error Handling
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
  console.error(err);
  res.status(500).send({
    message: "Internal Server Error"
  });
});

const startServer = () => {
  try {
    app.listen(env.PORT, () => {
      console.log(`${env.SERVICE_NAME} Server started on port ${env.PORT}`);
    });
  } catch (error) {
    console.error(error);
    process.exit(1);
  }
};

startServer();
