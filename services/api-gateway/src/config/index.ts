interface Config {
  PORT: number;
}

export const config: Config = {
  PORT: Number(process.env.PORT) || 3000
};
