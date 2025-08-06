#!/usr/bin/env node

/**
 * Testeo simple del servidor MCP - solo verifica que inicie correctamente
 */

import { spawn } from "child_process";

const testConfig = {
  BOOKSTACK_BASE_URL: "http://localhost:8000",
  BOOKSTACK_TOKEN_ID: "test_token_id",
  BOOKSTACK_TOKEN: "test_token_secret",
};

console.log("🚀 Iniciando servidor MCP...");

const serverProcess = spawn("node", ["build/index.js"], {
  stdio: ["pipe", "pipe", "pipe"],
  env: { ...process.env, ...testConfig },
});

let hasStarted = false;

if (serverProcess.stderr) {
  serverProcess.stderr.on("data", (data) => {
    const message = data.toString();
    console.log("📡", message.trim());

    if (message.includes("BookStack MCP Server started successfully")) {
      hasStarted = true;
      console.log("\n✅ ¡Servidor MCP iniciado correctamente!");
      console.log("💡 El servidor está listo para recibir solicitudes MCP");
      console.log(
        "🔧 Puedes conectarlo a Claude Desktop o probarlo con el script test-mcp.js"
      );

      // Mantener vivo por unos segundos para mostrar info
      setTimeout(() => {
        console.log("\n🛑 Cerrando servidor de prueba...");
        serverProcess.kill();
        process.exit(0);
      }, 3000);
    }
  });
}

if (serverProcess.stdout) {
  serverProcess.stdout.on("data", (data) => {
    console.log("📤", data.toString().trim());
  });
}

serverProcess.on("error", (error) => {
  console.log("❌ Error al iniciar el servidor:", error.message);
  process.exit(1);
});

serverProcess.on("close", (code) => {
  if (!hasStarted) {
    console.log(`❌ El servidor no pudo iniciar (código: ${code})`);
    console.log("💡 Verifica la configuración de BookStack");
  }
});

// Timeout de seguridad
setTimeout(() => {
  if (!hasStarted) {
    console.log("⏰ Timeout - el servidor no respondió");
    serverProcess.kill();
    process.exit(1);
  }
}, 10000);
