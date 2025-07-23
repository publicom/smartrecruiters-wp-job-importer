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
  - Set the API endpoint URL (example: https://api.smartrecruiters.com/v1/companies/{company}/postings).
  - Choose update frequency.
  - Optionally enable “Delete missing jobs” to keep data in sync.
5. Click Import Now or wait for the scheduled cron job.

## ✅ Using with Elementor
- Create a Single Template for the SR Jobs post type.
- Use Dynamic Fields to display:
  - Job Description (post_content)
  - Contract Type (contract_type)
  - Location (location)
  - Department (department)
  - Apply button (apply_url)

- For job listings:
  - Use Elementor's Archive Template feature.
  - Or use the Elementor Loop Builder.
 
## ⚠️ Requirements
- WordPress 6.x+
- PHP 7.4+ (tested up to PHP 8.4)
- Elementor (optional, for templates)

## 🛠 Developer Notes
- Each job from SmartRecruiters API is stored as a custom post for SEO optimization.
- All raw job data is stored as meta fields for advanced customization.

## 📌 Roadmap
- Shortcodes for job listing and details.
- Elementor widgets for drag-and-drop integration.
- Ability to filter jobs by department or location on the frontend.

## Made with ❤️ by:
Publicom
10 Route de Galice, 13100 Aix-en-Provence, France
www.publicom.fr
