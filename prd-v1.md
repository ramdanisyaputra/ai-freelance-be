📄 PRD — AI Freelancer Assistant
1. Product Overview

Product Name
AI Freelancer Assistant

Product Type
Web-based AI Tool (SaaS-ready)

Goal Utama
Membantu freelancer web developer:

Memahami brief klien dengan cepat

Menghasilkan scope kerja

Menentukan estimasi waktu & harga

Membuat proposal siap kirim

Primary Value

Mengurangi waktu bikin proposal dari ±30 menit → < 3 menit

2. Target User
🎯 Primary User

Freelancer Web Developer Indonesia

Stack: Laravel / React / Fullstack

Klien: UMKM, personal, startup kecil

Sistem harga: Fixed price (default)

Secondary (Future)

Agency kecil

Freelancer non-tech (writer, designer)

3. User Flow (High Level)
Login
 ↓
Input Brief Klien
 ↓
Generate AI Proposal
 ↓
Review & Copy

4. Core Features (MVP Only)
4.1 Input Brief Klien

Deskripsi
User memasukkan brief klien dalam bentuk text.

Requirement

Textarea input

Minimal 50 karakter

Bahasa bebas (Indonesia / campur Inggris)

4.2 AI Brief Analysis

Deskripsi
AI menganalisa apakah brief cukup jelas.

Output

Ringkasan kebutuhan klien

Deteksi ambiguity

Rules

Jika brief ambigu → tampilkan pertanyaan klarifikasi

Jika jelas → lanjut ke scope & proposal

4.3 Scope Generator

Deskripsi
Menghasilkan daftar scope pekerjaan yang realistis.

Contoh Output

Landing page

Halaman layanan

Admin panel sederhana

4.4 Estimation Generator

Deskripsi
Menghasilkan estimasi waktu & harga berdasarkan:

Scope

Profil freelancer

Output

{
  "duration_days": 21,
  "price": 7500000,
  "currency": "IDR"
}

4.5 Proposal Generator

Deskripsi
AI menghasilkan proposal siap kirim via WhatsApp / Email.

Tone

Profesional

Santai

Bahasa Indonesia

5. Non-Goals (Explicitly Out of Scope)

❌ Payment / Invoice
❌ PDF Export
❌ Multi-language
❌ CRM
❌ Client management

Ini sengaja supaya MVP cepat launch

6. Functional Requirements
6.1 Authentication

Laravel Sanctum

Email + password

1 user = 1 freelancer profile

6.2 Freelancer Profile

Disimpan di Laravel:

Skill stack

Rate type (fixed / hourly)

Minimal harga

Currency

6.3 AI Execution

Semua AI logic dijalankan di FastAPI

Laravel bertindak sebagai gateway & validator

Next.js tidak boleh memanggil FastAPI langsung

7. Technical Architecture
7.1 Stack
Layer	Tech
Frontend	Next.js
Backend Core	Laravel
AI Engine	FastAPI + LangGraph
Auth	Laravel Sanctum
DB	MySQL / PostgreSQL
7.2 Service Boundary
FastAPI

LangGraph

Prompt & agent

Stateless execution

Laravel

Auth

User & profile

Usage tracking

Data persistence

Next.js

UI

UX

API consumption (Laravel only)

8. API Contract
Request (Laravel → FastAPI)
{
  "brief": "Saya mau bikin website company profile...",
  "freelancer_profile": {
    "stack": ["Laravel", "React"],
    "rate_type": "fixed",
    "min_price": 5000000,
    "currency": "IDR"
  }
}

Response (FastAPI → Laravel)
{
  "need_clarification": false,
  "summary": "...",
  "scope": ["Landing page", "Admin panel"],
  "estimation": {
    "duration_days": 21,
    "price": 7500000
  },
  "proposal": "Halo Pak/Bu..."
}

9. LangGraph Requirements
State

brief

freelancer_profile

summary

unclear_points

scope

estimation

proposal

need_clarification

Flow

Analyze → Conditional → Scope → Estimation → Proposal

10. UX Requirements (Next.js)
Pages

Login

Dashboard

Generate Proposal

Result Page

UX Rules

Loading state saat AI proses

Copy button untuk proposal

Editable result (post-AI)

11. Success Metrics (MVP)

Proposal generated < 10 detik

1 user bisa generate > 3 proposal

User bisa langsung copy tanpa edit besar

12. Future Enhancements (Post-MVP)

PDF export

Custom tone

Proposal history

Follow-up message generator

Agency mode

13. Risks & Mitigation
Risk	Mitigation
Prompt tidak konsisten	Prompt versioning
Estimasi terlalu mahal	Min price clamp
Brief ambigu	Mandatory clarification step
14. Definition of Done (MVP)

✅ User login
✅ Input brief
✅ AI response valid
✅ Proposal bisa di-copy
✅ Data tersimpan di DB