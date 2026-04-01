import express from "express";
import type { Request, Response } from "express";
import { config } from "./config/index.js";

const app = express();
app.use(express.json());

app.get("/", (req: Request, res: Response) => {
  res.send("Hello World!");
});

app.listen(config.PORT, () => {
  console.log(`Example app listening on port ${config.PORT}`);
});
