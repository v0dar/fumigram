# 🔥 Fumigram — Pixel Media Social Network Platform

> A full-featured, self-hosted social media platform built with PHP & MySQL. Share photos, videos, stories, and connect with the world — on your own server.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)
[![Contributors](https://img.shields.io/github/contributors/v0dar/fumigram)](https://github.com/v0dar/fumigram/graphs/contributors)

---

## 📸 Demo

- 🌐 **Live Version:** [fumigram.com](https://fumigram.com)
- 🧪 **Demo Site:** [fumigram.free.nf](https://fumigram.free.nf/)
- 💬 **Telegram Community:** [Join Here](https://t.me/vidarbuilds)

---

## 🧪 Demo Credentials

The demo comes pre-loaded with **300+ dummy users**, posts, messages, stories, and interactions so you can explore the platform fully without setting anything up.

### 👥 Sample Users

| Username | Membership |
|---|---|
| Stephen | Free Member |
| Lagata | Free Member |
| Connie | Free Member |
| Mikasa | Free Member |
| Akari | Free Member |
| Merry | Free Member |
| James | Free Member |
| Kotlin | Free Member |
| Andy | Free Member |
| Blessing | Free Member |
| Luna | Free Member |
| Ario | ⭐ Premium |
| Cortana | ⭐ Premium |
| Elena | ⭐ Premium |
| Isabella | Free Member |
| Mira | Free Member |
| Neko | Free Member |
| mina | Free Member |
| Janet | Free Member |
| *...and 300+ more* | |

### 🔑 Passwords to Try

Each user's password is one of the following — just try them until one works:

```
typing
Typing
password
Password
```

> 💡 Example: to log in as `Elena`, try username `Elena` with password `Password`, `password`, `Typing`, or `typing`.

---

## 🖼️ Screenshots

<table>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/e794c96a-696a-4319-805a-cac6b0587c38" alt="Home Feed" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/f9e56e3b-d10f-4c74-8c15-8bcfadb680ca" alt="Explore" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/f1fb4dc6-dc72-41b2-ab6b-60faea59c8e8" alt="Profile Page" width="100%"/></td>
  </tr>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/5d1792b2-26d8-4521-bb27-153aaa4cacbe" alt="Post View" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/dd862f75-746b-46b6-be3f-424a9eb8008f" alt="Stories" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/42f861c1-0d57-4424-b897-97c8674dc527" alt="Mobile View" width="100%"/></td>
  </tr>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/52f24a4d-f766-4ecd-99fc-e66172f04b7d" alt="Messaging" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/5024edc8-ff6a-4af6-a39b-a6f2e0553091" alt="Tiles View" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/23560114-8e59-4f10-901d-a7e0dd4c9ef3" alt="Upload" width="100%"/></td>
  </tr>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/32ce8d82-add3-4cd2-b847-787fbcca719e" alt="Settings" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/b520f4c5-08cd-406c-8c4e-cfe18aebed34" alt="Premium" width="100%"/></td>
    <td><img src="https://github.com/user-attachments/assets/28dd946d-ae3b-4f46-a054-50abdabe889f" alt="Admin Panel" width="100%"/></td>
  </tr>
</table>

---

## ✨ Features

### 👤 User Authentication & Profiles
- Registration, login, email verification, and password recovery
- Account deactivation/reactivation
- Profile pages with avatar management and privacy controls
- New user startup wizard (avatar setup, follow suggestions)

### 📸 Posts & Content
- Image posts (multi-image support), video posts (YouTube, Vimeo, Dailymotion, MP4), GIFs, and text posts
- Private/paid photo posts with blurred previews
- Comments, reactions, sharing, saving, and bookmarking
- Hashtag support for discovery and categorization
- 24-hour Stories with view tracking

### 🏠 Feed & Discovery
- Personalized home timeline
- Explore page and trending content
- Image tile grid view
- Activity feed and notification center

### 💬 Messaging
- Private one-on-one chat
- Media sharing in conversations (images, videos, files)
- Online status indicators
- Real-time message notifications

### 💰 Monetization
- Premium membership (Pro) with badge and exclusive content
- **Points/Rewards System** — earn points for activity (uploading, engaging)
- **Tokens System** — purchasable token packs used to redeem lost points:
  - 🪙 Rain of Tokens — 20,000 tokens for 20 Credits
  - 💰 Bag of Tokens — 60,000 tokens for 33 Credits
  - 🎁 Chest of Tokens — 120,000 tokens for 60 Credits
- **Boosts System** — promote posts to reach more users (each boost lasts 3 days):
  - ⚡ Single Boost — 1 boost for 2 Credits
  - ⚡ Crate of Boost — 24 boosts for 50 Credits
  - ⏱️ Boost Timer — 3 timer boosts for 560 Credits
- Paid post unlocking (private photos behind a paywall)
- Advertisement management system
- **Payment Gateways:** Stripe, PayPal, Paystack, CoinPayments, 2Checkout

### 🗄️ Media & Storage
- Image resizing, compression, and video transcoding (FFmpeg)
- Blurred previews for private content
- **Storage options:** Local, FTP, Amazon S3, Google Cloud Storage, DigitalOcean Spaces

### 🔔 Notifications
- In-app real-time alerts
- Email notifications
- Push notifications via OneSignal

### 🛡️ Admin Panel (cpanel)
- Full user management and content moderation
- System-wide settings and configuration
- Analytics, ad management, blacklist/ban system
- XSS protection and content filtering

### ⚙️ Technical
- Responsive, mobile-friendly design
- Light and dark theme support
- Multi-language support
- MySQL database with session management and caching
- RESTful API endpoints
- Cron job support and error logging
- Cookie consent (GDPR)

---

## 📁 Folder Structure

```
fumigram/
├── apps/               # Main application (themes & views)
│   └── viga/        # Default theme
│       ├── 404/
│       ├── activate/
│       ├── boosts/
│       ├── explore/
│       ├── home/
│       ├── login/
│       ├── messages/
│       ├── posts/
│       ├── premium/
│       ├── profile/
│       ├── settings/
│       ├── signup/
│       ├── startup/
│       ├── timeline/
│       ├── upload/
│       └── ...
├── core/               # Core framework and logic
├── cpanel/             # Admin panel
│   ├── si/
│   ├── uis/
│   └── index.php
├── help/               # Help center pages
├── install/            # Web installer
├── media/              # Uploaded media files
├── index.php           # Application entry point
├── load.php            # Core loader
├── ajax.php            # AJAX handler
├── social.php          # Social features handler
├── command.php         # CLI commands
├── fumigram.sql        # Database schema
├── nginx.server.conf   # Nginx config example
├── php.ini             # Recommended PHP settings
└── README.md
```

---

## 🚀 Installation

### Requirements
- PHP **7.4 or higher** (with `shell_exec` enabled for video processing)
- MySQL **5.7 or higher**
- A web server: Apache or Nginx
- FFmpeg (optional, for video transcoding)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/v0dar/fumigram.git
   cd fumigram
   ```

2. **Upload files** to your server's `public_html` (or web root) directory via FTP or your hosting control panel.

3. **Create a MySQL database** and note your credentials.

4. **Run the installer** by visiting:
   ```
   https://yourdomain.com/install
   ```
   Follow the on-screen steps to configure your database connection and admin account.

5. **Import the database** (if not done via the installer):
   ```bash
   mysql -u your_user -p your_database < fumigram.sql
   ```

6. **Configure your web server.** For Nginx, refer to `nginx.server.conf` included in the repo.

7. **Set up SMTP** for email delivery via the Admin Panel:
   `Cpanel → Settings → Setup Emails`

8. **(Optional) Configure media storage** (Amazon S3, DigitalOcean Spaces, Google Cloud, or FTP) via the Admin Panel.

9. **Delete the `/install` folder** after setup is complete for security.

---

## 📧 SMTP Configuration

Go to `Cpanel → Settings → Setup Emails` and fill in:

| Field | Value |
|---|---|
| SMTP Host | `mail.yourdomain.com` |
| SMTP Username | Your email address |
| SMTP Password | Your email password |
| SMTP Port | `587` (TLS) or `465` (SSL) |
| SMTP Encryption | TLS or SSL |

> 💡 Tip: Using your server's IP address as the SMTP host tends to improve deliverability.

---

## 🎬 FFmpeg Setup (Video Processing)

FFmpeg is required to transcode non-MP4 video uploads.

1. Go to `Cpanel → File Upload System → FFMPEG Configuration`
2. Enable FFmpeg. The default bundled path is `./ffmpeg/ffmpeg`
3. If video conversion fails, install FFmpeg on your server and update the path to `/usr/bin/ffmpeg`

> Ensure `shell_exec` is enabled in your `php.ini`.

---

## 🤝 Contributing

Contributions are warmly welcome! Whether it's a bug fix, new feature, translation, or documentation improvement — all pull requests are reviewed and appreciated.

### How to Contribute

1. **Fork** the repository
2. **Create a branch** for your feature or fix:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes** and commit with a clear message:
   ```bash
   git commit -m "feat: add [description of change]"
   ```
4. **Push** your branch:
   ```bash
   git push origin feature/your-feature-name
   ```
5. **Open a Pull Request** against the `main` branch and describe what you changed and why.

### Contribution Guidelines

- Keep pull requests focused — one feature or fix per PR
- Follow the existing code style and structure
- Test your changes before submitting
- Update documentation if your change affects setup or configuration
- Be respectful and constructive in code reviews

### Ideas for Contributions

- 🌍 New language translations
- 🎨 New themes or UI improvements
- 🔌 New payment gateway integrations
- 🐛 Bug reports and fixes
- 🔒 Security improvements
- 📖 Documentation updates

---

## 🐛 Reporting Issues

Found a bug? Please [open an issue](https://github.com/v0dar/fumigram/issues) and include:
- A clear description of the problem
- Steps to reproduce it
- Your PHP version, server type, and OS
- Any relevant error log output

---

## 💬 Community & Support

- 💬 **Telegram:** [t.me/vidarbuilds](https://t.me/vidarbuilds)
- 🧑‍💻 **Maintainer:** [@v0dar](https://github.com/v0dar)

> Support response times: within 12–24 hours on weekdays (UTC+01:00). Up to 48 hours in some cases.

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

You are free to use, modify, and distribute this software for personal or commercial projects. Attribution is appreciated but not required.

---

<p align="center">Made with ❤️ by <a href="https://github.com/v0dar">v0dar</a></p>
