#!/usr/bin/env node

/**
 * Script de prueba para el servidor MCP de BookStack
 * Este script simula la comunicación MCP para testear las herramientas
 */

import { spawn } from "child_process";
import { createInterface } from "readline";

// Configuración de prueba - ajusta estos valores según tu setup
const testConfig = {
  BOOKSTACK_BASE_URL: "http://localhost:8000",
  BOOKSTACK_TOKEN_ID: "ANShHaHXBkTCb1BJsRub0OqXhr9rPktD",
  BOOKSTACK_TOKEN: "RdRx0NpwPRrqdU2LHO6l8TZp2yZkntwc",
};

console.log("🚀 Iniciando prueba del servidor MCP de BookStack...\n");

// Función para testear una herramienta específica
async function testTool(toolName, args = {}) {
  return new Promise((resolve, reject) => {
    console.log(`📋 Testeando herramienta: ${toolName}`);
    console.log(`📥 Argumentos:`, JSON.stringify(args, null, 2));

    // Spawn del servidor MCP
    const serverProcess = spawn("node", ["build/index.js"], {
      stdio: ["pipe", "pipe", "pipe"],
      env: { ...process.env, ...testConfig },
    });

    let responseData = "";
    let errorData = "";

    // Escuchar respuestas
    serverProcess.stdout.on("data", (data) => {
      responseData += data.toString();
    });

    serverProcess.stderr.on("data", (data) => {
      errorData += data.toString();
    });

    serverProcess.on("close", (code) => {
      if (code === 0) {
        console.log(`✅ Respuesta:`, responseData || "Sin respuesta stdout");
        if (errorData) console.log(`ℹ️  Info:`, errorData);
        resolve({ success: true, data: responseData, info: errorData });
      } else {
        console.log(`❌ Error (código ${code}):`, errorData);
        reject({ success: false, error: errorData, code });
      }
    });

    // Simular solicitud MCP para listar herramientas
    const listToolsRequest = {
      jsonrpc: "2.0",
      id: 1,
      method: "tools/list",
    };

    // Simular solicitud MCP para llamar herramienta
    const callToolRequest = {
      jsonrpc: "2.0",
      id: 2,
      method: "tools/call",
      params: {
        name: toolName,
        arguments: args,
      },
    };

    // Enviar solicitudes
    serverProcess.stdin.write(JSON.stringify(listToolsRequest) + "\n");
    serverProcess.stdin.write(JSON.stringify(callToolRequest) + "\n");
    serverProcess.stdin.end();

    // Timeout de seguridad
    setTimeout(() => {
      serverProcess.kill();
      reject({ success: false, error: "Timeout", code: -1 });
    }, 10000);
  });
}

// Función para probar la conectividad básica
async function testConnectivity() {
  console.log("🔗 Verificando conectividad con BookStack...\n");

  try {
    // Test simple: listar libros
    await testTool("list_books", { count: 5 });
    console.log("✅ Conectividad exitosa!\n");
    return true;
  } catch (error) {
    console.log("❌ Error de conectividad:", error.error);
    console.log("\n💡 Verifica que:");
    console.log(
      "   - BookStack esté ejecutándose en",
      testConfig.BOOKSTACK_BASE_URL
    );
    console.log("   - Los tokens de API sean correctos");
    console.log("   - El usuario tenga permisos de API\n");
    return false;
  }
}

// Menú interactivo
async function showMenu() {
  const rl = createInterface({
    input: process.stdin,
    output: process.stdout,
  });

  console.log("🧪 ¿Qué quieres testear?");
  console.log("1. Verificar conectividad");
  console.log("2. Listar libros");
  console.log("3. Listar páginas");
  console.log("4. Buscar contenido");
  console.log("5. Crear libro de prueba");
  console.log("6. Listar usuarios");
  console.log("7. Salir");
  console.log("");

  const choice = await new Promise((resolve) => {
    rl.question("Elige una opción (1-7): ", resolve);
  });

  rl.close();

  try {
    switch (choice) {
      case "1":
        await testConnectivity();
        break;
      case "2":
        await testTool("list_books", { count: 10 });
        break;
      case "3":
        await testTool("list_pages", { count: 10 });
        break;
      case "4":
        await testTool("search_all", { query: "test", count: 5 });
        break;
      case "5":
        await testTool("create_book", {
          name: "Libro de Prueba MCP",
          description: "Libro creado desde el servidor MCP para pruebas",
        });
        break;
      case "6":
        await testTool("list_users", { count: 5 });
        break;
      case "7":
        console.log("👋 ¡Hasta luego!");
        process.exit(0);
      default:
        console.log("❌ Opción no válida");
    }
  } catch (error) {
    console.log("❌ Error durante la prueba:", error);
  }

  console.log("\n" + "=".repeat(50) + "\n");
  await showMenu(); // Mostrar menú nuevamente
}

// Función principal
async function main() {
  console.log("⚙️  Configuración actual:");
  console.log(`   URL: ${testConfig.BOOKSTACK_BASE_URL}`);
  console.log(`   Token ID: ${testConfig.BOOKSTACK_TOKEN_ID}`);
  console.log(`   Token: ${testConfig.BOOKSTACK_TOKEN.substring(0, 8)}...`);
  console.log("\n" + "=".repeat(50) + "\n");

  await showMenu();
}

// Verificar configuración antes de empezar
if (
  !testConfig.BOOKSTACK_TOKEN_ID ||
  testConfig.BOOKSTACK_TOKEN_ID === "tu_token_id"
) {
  console.log("❌ Configuración incompleta!");
  console.log("📝 Edita el archivo test-mcp.js y configura:");
  console.log("   - BOOKSTACK_BASE_URL");
  console.log("   - BOOKSTACK_TOKEN_ID");
  console.log("   - BOOKSTACK_TOKEN");
  console.log(
    "\n💡 Obtén los tokens desde BookStack → Usuario → Preferencias → API Tokens"
  );
  process.exit(1);
}

main().catch(console.error);
