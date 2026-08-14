# 🤖 AI Chat Studio

> A self-hosted, multi-provider AI workspace, RAG Knowledge Engine, and Persona Studio built with **Laravel 13**, **PHP 8.4**, **MariaDB 11**, and **Docker**.

[![Author](https://img.shields.io/badge/Author-FreeDirt-black?style=for-the-badge&logo=github)](https://github.com/FreeDirt)
[![Laravel](https://img.shields.io/badge/Laravel-v13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-v8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![MariaDB](https://img.shields.io/badge/MariaDB-11.x-003545?style=for-the-badge&logo=mariadb&logoColor=white)](https://mariadb.org/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

---

## 📸 Overview

**AI Chat Studio** is an open-source, enterprise-grade AI interface designed to replace fragmented SaaS tools with a unified, private, self-hosted web app. It connects to **OpenAI**, **Anthropic Claude**, **Google Gemini**, **OpenRouter**, and **Local Ollama** models, providing real-time streaming, document Q&A (RAG), prompt engineering tools, and team collaboration.

---

## ✨ Key Features

### 🔌 Multi-Provider AI Engine
- **5 Supported AI Providers**: OpenAI (`gpt-4o`, `o3-mini`), Anthropic Claude (`claude-3-5-sonnet`, `opus`), Google Gemini (`gemini-2.5-flash`), OpenRouter (100+ models including DeepSeek, Llama 3, Mistral), and Ollama (local offline models).
- **Manual Model Override**: Type any model string manually to override dropdown defaults.
- **Connection Tester**: 1-click API key validation & latency check for all configured providers.
- **Provider Analytics**: Track token consumption, estimated costs, and latency across providers.

### ⚡ Real-Time Streaming & Interaction
- **Server-Sent Events (SSE)**: Word-by-word streaming typewriter responses with a pulsating streaming cursor (`▌`).
- **Voice Dictation (Mic 🎙️)**: Speech recognition for hands-free voice prompting.
- **Prompt Polish (✨)**: 1-click AI prompt enhancer to rewrite draft inputs into detailed, structured prompts.
- **Prompt Pattern Library (⚡)**: Quick-insert templates for Chain-of-Thought (CoT), Few-Shot, XML tags, ReAct, and JSON outputs.

### 📄 RAG Document Q&A System
- **Multi-Format Document Parsing**: Upload PDF, DOCX, TXT, MD, CSV, and code files (`.py`, `.js`, `.php`, `.ts`, `.cpp`, `.go`, etc.) up to 20MB.
- **Paragraph-Aware Chunking**: Intelligently splits documents into 1,000-character chunks with 200-character overlaps.
- **Multi-Provider Vector Embeddings**: Generate embeddings via OpenAI, OpenRouter, Gemini, or Ollama.
- **Zero-Dependency Fallback (`local-tfidf`)**: Built-in 256-dimensional Local Feature Vectorizer automatically kicks in if cloud embedding APIs or key limits fail.
- **Source Attribution Badges**: Displays document source chips with relevance percentage matching scores below AI responses.

### 🎭 Persona Studio & Advanced Prompt Engineering
- **Fullscreen Persona Studio**: Craft custom AI assistants with system prompts up to **65,536 characters**.
- **Dynamic Runtime Variables**: Auto-expands `@{{date}}`, `@{{time}}`, `@{{day}}`, `@{{datetime}}`, `@{{timezone}}` variables at execution time.
- **Persona Knowledge Base**: Attach RAG documents directly to a persona so every conversation inheriting the persona gains knowledge automatically.
- **Live Prompt Tester**: Test persona behavior against your active provider directly inside the studio before deploying.

### 🌿 Branching & Multi-Thread Conversation Trees
- **Message Branching (`🌿 Branch`)**: Branch off any user or assistant message to test alternative prompt variations.
- **Interactive Branch Navigation**: Seamlessly navigate tree branches with **`← 1/3 →`** pill buttons.

### 🔀 Side-by-Side Model Compare Mode
- **Compare Mode**: Send a single prompt simultaneously to two different models/providers and view side-by-side responses with live markdown rendering.

### 📥 One-Click 5-Format Export Engine
- **🖨️ PDF Report (`.pdf`)**: Printable HTML report with `@media print` optimized CSS and auto-print trigger.
- **🌐 HTML Document (`.html`)**: Self-contained dark-mode HTML file with metadata header and code block styling.
- **📝 Markdown (`.md`)**: Formatted Markdown document with author badges.
- **📄 Plain Text (`.txt`)**: Raw transcript export.
- **📦 JSON Backup (`.json`)**: Full structured database payload with token stats and message IDs.

### 🎨 Super Admin Workspace Branding Studio (`/admin/branding`)
- **Custom Branding**: Modify Workspace Title, Welcome Heading, and Subheading.
- **Logo Uploader**: Upload workspace logos (PNG, SVG, JPG, WEBP) stored directly in public storage.
- **Accent Swatches**: Select from 7 curated theme swatches (Violet, Royal Blue, Emerald Green, Amber Gold, Hot Pink, Deep Purple, Cyan Spark) or custom hex colors.
- **Live Preview Deck**: Real-time responsive preview deck.

### ⌨️ Keyboard Shortcuts & Navigation
- **`Ctrl+K` / `Cmd+K`**: Global command palette search across conversation titles and message contents.
- **`Ctrl+\` / `Cmd+\`**: Toggle left navigation sidebar.
- **`Ctrl+]` / `Cmd+]`**: Toggle right AI Personas & Bookmarks panel.
- **`?` or `Ctrl+/`**: Open interactive keyboard shortcuts cheat-sheet overlay.

---

## 🛠️ Stack & Architecture

| Layer | Technology |
|---|---|
| **Framework** | Laravel 13 |
| **Language** | PHP 8.4-FPM |
| **Database** | MariaDB 11 |
| **Web Server** | Nginx Alpine |
| **Containerization** | Docker & Docker Compose |
| **Styling** | Vanilla CSS Design System (Zero Tailwind dependency) |
| **Markdown & Code** | Marked.js + Highlight.js |
| **Streaming** | Server-Sent Events (SSE) |

---

## 🚀 Quick Start (Docker)

### 1. Launch Container
```bash
./start.sh
```

### 2. Access the Application
Open your browser and navigate to:
👉 **`http://localhost:8015`**

---

## 📄 License

This project is open-source software licensed under the **[MIT License](LICENSE)**.
