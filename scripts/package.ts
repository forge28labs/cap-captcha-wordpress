import { readFileSync } from "fs";
import { globSync } from "glob";
import { resolve, relative } from "path";
import { zipSync, strToU8 } from "fflate";
import { writeFileSync, mkdirSync, rmSync } from "fs";

const PLUGIN_DIR = "cap-captcha";
const DIST_DIR = "dist";

function getVersion(): string {
  const file = readFileSync(`${PLUGIN_DIR}/cap-captcha.php`, "utf-8");
  const match = file.match(/Version:\s*([0-9.]+)/i);

  if (!match) throw new Error("Could not find plugin version");
  return match[1]!;
}

const version = getVersion();
console.log(`📦 Version: ${version}`);

const zipName = `cap-captcha-v${version}.zip`;
const outputPath = resolve(DIST_DIR, zipName);

rmSync(outputPath, { force: true });
mkdirSync(DIST_DIR, { recursive: true });

const IGNORE = [
  "**/.git/**",
  "**/.github/**",
  "**/.gitignore",
  "**node_modules/**",
  "**/dist/**",
  "**/*.zip",
];

const files = globSync(`${PLUGIN_DIR}/**/*`, {
  dot: true,
  nodir: true,
  ignore: IGNORE,
});

console.log(`📁 Packing ${files.length} files...`);

const zipInput: Record<string, Uint8Array> = {};

for (const file of files) {
  const data = readFileSync(file);
  const pathInZip = `${PLUGIN_DIR}/${relative(PLUGIN_DIR, file).replaceAll("\\", "/")}`;
  zipInput[pathInZip] = new Uint8Array(data);
}

const zipped = zipSync(zipInput, { level: 9 });

writeFileSync(outputPath, zipped);

console.log(`✔ Created ${zipName}`);
