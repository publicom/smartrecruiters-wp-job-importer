# SmartRecruiters Job Importer for WordPress

A WordPress plugin that imports job postings from the [SmartRecruiters API](https://developers.smartrecruiters.com/docs/endpoints) into a **Custom Post Type** (`SR Jobs`).  
Ideal for creating an SEO-friendly **Career Page** with Elementor or fallback templates.

---

## ✅ Features
- Fetch jobs from SmartRecruiters API and store as **custom posts**.
- Custom Post Type: **SR Jobs**.
- Auto-save key fields as custom fields:
  - `contract_type`
  - `location`
  - `department`
  - `apply_url`
- **Elementor ready**:
  - Dynamic Tags for all custom fields.
  - Easily build Single and Archive templates.
- **Fallback templates included**:
  - Archive with department filter (JS)
  - Single job page with Apply button.
- **Shortcodes**:
  - `[sr_jobs_list]` → Job listing with filter.
  - `[sr_job_detail id="123"]` → Single job view.
- Cron scheduling or manual import.
- SEO-friendly (each job is a page).

---

## 🚀 Installation
1. Download or clone the plugin:
   ```bash
   git clone https://github.com/publicom/smartrecruiters-wp-job-importer.git
   ```
2. Upload to `wp-content/plugins/`.
3. Activate via **Plugins** in WordPress.
4. Go to **SR Jobs Import**:
   - Enter API Endpoint (e.g., `https://api.smartrecruiters.com/v1/companies/{company}/postings`).
   - Choose update frequency.
   - Optionally enable “Delete Missing Jobs”.
5. Click **Import Now** or wait for the cron schedule.

---

## ✅ Elementor Integration
- Create a **Single Template** for `SR Jobs`:
  - Job Description → `post_content`
  - Contract Type → `contract_type`
  - Location → `location`
  - Department → `department`
  - Apply button → `apply_url`
- Use Elementor’s **Archive Template** for listings.

---

## ✅ Without Elementor (Fallback)
- Use built-in templates:
  - `archive-sr_job.php`
  - `single-sr_job.php`
- Or use shortcodes:
  - `[sr_jobs_list]` → Grid + JS filter by department.
  - `[sr_job_detail id="123"]`.

---

## ⚠️ Requirements
- WordPress 6.x+
- PHP 7.4+ (tested up to PHP 8.4)

---

## 🛠 Developer Notes
- All SmartRecruiters API fields stored as meta for customization.
- Fallback templates use Bootstrap-like structure for easy styling.

---

## 📌 Roadmap
- Elementor widgets for Job Grid and Single Job.
- AJAX-based filtering and pagination.
- Settings UI improvements.

---

### Developed by:
**Publicom**  
10 Route de Galice, 13100 Aix-en-Provence, France  
[www.publicom.fr](https://www.publicom.fr)  
[Instagram](https://www.instagram.com/agence_publicom/)
