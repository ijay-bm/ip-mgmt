import prettier from "eslint-config-prettier";
import vuetify from "eslint-config-vuetify";
import prettierPlugin from "eslint-plugin-prettier";

export default vuetify(
  {
    ts: true,
  },
  {
    plugins: {
      prettier: prettierPlugin,
    },
    rules: {
      "vue/script-indent": "off",
      "@stylistic/semi": "off",
      "@stylistic/quotes": "off",
      "@stylistic/comma-dangle": "off",
      "@stylistic/arrow-parens": "off",
      // "perfectionist/sort-imports": "off",
      // "perfectionist/sort-named-imports": "off",
      "prettier/prettier": "error",
    },
  },
  prettier,
);
