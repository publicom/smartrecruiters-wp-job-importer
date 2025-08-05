# SmartRecruiters Job Importer for WordPress

A WordPress plugin that imports job postings from the [SmartRecruiters API](https://developers.smartrecruiters.com/docs/endpoints) into a **Custom Post Type** (`SR Jobs`).  
Perfect for building an SEO-friendly **Career Page** with Elementor or fallback templates.

---

## ✅ Features
- Fetch job offers from SmartRecruiters API and store them as **custom posts**.
- **Custom Post Type**: `SR Jobs`.
- Stores and updates key fields as **custom meta**:
  - `contract_type`
  - `rythme` (Employment type)
  - `location`
  - `department`
  - `apply_url`
- **Elementor Ready**:
  - Dynamic Tags for all custom fields.
  - Build your Single and Archive templates easily.
- **Fallback Templates Included**:
  - `archive-sr_job.php` → Job list with department filter.
  - `single-sr_job.php` → Displays full job description and meta.
- **Automatic or Manual Import**:
  - **Manual**: Import Now button.
  - **Automatic**: Cron-based (frequency configurable).
- **Selective Import**:
  - Filter jobs by department from admin.
- **SEO-friendly** (each job = its own page).
- **Video Support**:
  - Detects videos from `jobAd.sections.videos` and embeds YouTube automatically.
- **Handles Updates**:
  - Updates existing jobs based on SmartRecruiters `id`.
  - Removes jobs missing from the API (optional).

---

## 🚀 Installation
1. Download or clone the plugin:
   ```bash
   git clone https://github.com/publicom/smartrecruiters-wp-job-importer.git
   ```
2. Upload to `wp-content/plugins/`.
3. Activate via **Plugins** in WordPress.
4. Configure under **Jobs > Settings**:
   - API Endpoint:  
     Example → `https://api.smartrecruiters.com/v1/companies/{company}/postings`
   - Select update frequency.
   - Optionally enable **Delete Missing Jobs**.
   - (Optional) Select departments to import.
5. Click **Import Now** or wait for cron.

---

## ✅ Elementor Integration
- Create a **Single Template** for `SR Jobs`:
  - Job Description → `post_content`
  - Contract Type → `contract_type`
  - Employment Type → `rythme`
  - Location → `location`
  - Department → `department`
  - Apply button → `apply_url`
- Use Elementor’s **Archive Template** for job listing.

---

## ✅ Without Elementor (Fallback)
- Use built-in templates:
  - `archive-sr_job.php` → Job list with JS filter by department.
  - `single-sr_job.php` → Full job view with Apply button and video embeds.

---

## ⚠️ Requirements
- WordPress 6.x+
- PHP 7.4+ (tested up to PHP 8.4)
- SmartRecruiters API Key / Public Endpoint

---

## 🛠 Developer Notes
- Stores all SmartRecruiters fields in **custom meta** for advanced customization.
- Uses `wp_kses` with iframe whitelist to allow YouTube embeds.
- Compatible with **Elementor Dynamic Tags** for custom fields.
- Jobs update automatically if existing (`_srji_ref` meta key).
- Option to remove jobs not present in the API (safe cleanup).

---

## ✅ Recent Changes
- Added **video support** from `jobAd.sections.videos` (YouTube embeds + fallback link).
- Whitelisted `<iframe>` for secure display of video content.
- Added department filter UI in admin.
- Improved UX: removed manual post creation option for SR Jobs.

---

## 📌 Roadmap
- Elementor widgets for job grid and single job display.
- Advanced filtering (AJAX) and pagination.
- Progress bar during manual import.

---

### Developed by:
**Publicom**  
10 Route de Galice, 13100 Aix-en-Provence, France  
[www.publicom.fr](https://www.publicom.fr)  
[Instagram](https://www.instagram.com/agence_publicom/)
