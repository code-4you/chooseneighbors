# Choose Neighbors

**Homes and communities designed around friendship — people choose their neighbours. That's all. Everything else is supplementary.**

This is the full source of [chooseneighbors.com](https://chooseneighbors.com): a working PHP site, an essay series on community design, guides, and live experiments — built around one idea: the most important thing about a home is who lives around it, and it's the one thing the housing market never lets you choose.

## What's in here

- **The dynamic map model** — the core mechanic ([read it](https://chooseneighbors.com/belonging/choose-your-neighbours.html)): people request to live near their people through a matching app, space is deliberately kept open so groups can grow, anyone can move at any time, and a nearest-neighbours program lays the place out by real bonds. [Every fragment of it exists somewhere](https://chooseneighbors.com/the-fragments.html); the assembly is the project.
- **The Belonging essay series** (`belonging/`) — homes you own never rent, governing ourselves, keeping people safe, what makes communities last, and the evidence for why proximity to chosen people changes bodies and lives.
- **Guides** — [Start a community with your friends](https://chooseneighbors.com/start-a-community), [Cohousing vs coliving](https://chooseneighbors.com/cohousing-vs-coliving.html), and [Find a community](https://chooseneighbors.com/find-a-community) — an AI-populated directory of real communities by state (Gemini-backed, cached in MySQL).
- **The app** — a small PHP community platform: listings, groups, messages, profiles (username + display name), Google sign-in.
- **Experiments** — AI test engines, city experiments (the 766-page journal PDF), an [organizer / counselling app demo](https://chooseneighbors.com/organiser-counselling-app.html).

## Running it

Plain PHP 8 + MySQL on any LAMP-style host.

1. Copy the files to your web root (`.htaccess` provides extensionless URLs on Apache/LiteSpeed).
2. `cp config.example.php config.php` and fill in your values (DB, Google client ID, reCAPTCHA, ImgBB, Gemini, SMTP). **Never commit `config.php`.**
3. Most tables are created by the code on first use; the community-directory tables self-create on first visit to `/find-a-community`.

## License — use it, credit it, share success

Everything here is free to use, **including commercially**, with credit — and if what you build with it passes **US$1,000,000 lifetime gross revenue**, a **5% royalty applies above that line** (the Unreal Engine shape). The ideas themselves are unconditionally free — build communities, that's the point.

**Contributions of real value may share in those royalties** — agreed case by case, in writing. See [LICENSE.md](LICENSE.md).

## Contact

Via [chooseneighbors.com/support](https://chooseneighbors.com/support) — phone or WhatsApp listed there. If you're isolated and this idea speaks to you: [the pitch note](https://chooseneighbors.com/pitch-note-2026.html) is why this exists.
