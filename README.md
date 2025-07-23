# SmartRecruiters Job Importer for WordPress

A WordPress plugin that imports job postings from the [SmartRecruiters API](https://developers.smartrecruiters.com/docs/endpoints) into a **Custom Post Type** (`SR Jobs`).  
Perfect for creating an SEO-friendly **Career Page** with **Elementor templates** and dynamic fields.

---

## ✅ Features

- Fetch jobs from SmartRecruiters API and store them as **custom posts**.
- Creates a custom post type: **SR Jobs**.
- Automatically saves key fields as **Custom Fields**:
  - `contract_type`
  - `location`
  - `department`
  - `apply_url`
- Elementor-compatible (Dynamic Tags support).
- Categories auto-assigned based on job department.
- Manual import button + cron scheduling (hourly, twice daily, daily).
- SEO-ready job pages.
- Easy to style and integrate into any theme.

---

## 🔧 Planned Features (Next Version)
- **Tooltip/help** for API endpoint field in admin settings.
- **Frontend listing page** with department filtering.
- **Single job template** optimized for Elementor.
- Shortcodes for listing and job detail (fallback if Elementor is not used).

---

## 🚀 Installation

1. Download the plugin or clone the repository:
   ```bash
   git clone https://github.com/YOUR-USERNAME/smartrecruiters-wp-job-importer.git
2. Upload the folder to wp-content/plugins/.
3. Activate SmartRecruiters Job Importer from your WordPress Plugins menu.
4. Go to SR Jobs Import in the admin menu:
Set the API endpoint URL (example: https://api.smartrecruiters.com/v1/companies/{company}/postings).
Choose update frequency.
Optionally enable “Delete missing jobs” to keep data in sync.
5. Click Import Now or wait for the scheduled cron job.
