import { dirname } from "node:path";
import { fileURLToPath } from "node:url";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const compat = new FlatCompat({
  baseDirectory: __dirname
});

const eslintConfig = [
  {
    // out/ and live/ are generated build output (minified bundles); linting them
    // buries real findings under thousands of warnings from compiled code.
    ignores: [
      ".next/**",
      "out/**",
      "live/**",
      "next-env.d.ts",
      "node_modules/**"
    ]
  },
  ...compat.extends("next/core-web-vitals", "next/typescript")
];

export default eslintConfig;
