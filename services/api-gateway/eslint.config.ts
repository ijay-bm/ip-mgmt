import * as globals from "globals";
import tseslint from "typescript-eslint";
import { defineConfig } from "eslint/config";
import * as prettier from "eslint-config-prettier";

export default defineConfig([
  { files: ["**/*.{js,mjs,cjs,ts,mts,cts}"], languageOptions: { globals: globals.node } },
  tseslint.configs.recommended,
  prettier
]);
