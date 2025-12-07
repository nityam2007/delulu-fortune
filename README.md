# delulu fortune

your daily cosmic nonsense. get a fortune that means nothing but feels nice.

**live:** [delulu.nytm.in](https://delulu.nytm.in)

## what is this

- ai generates 5 fortunes daily at midnight
- you get one random fortune
- same fortune sticks with you for 4-6 hours
- share with friends, spread the delusion

## setup

```bash
# add your openai key
nano includes/config.php

# run locally
php -S localhost:8000
```

## deploy

1. edit `includes/config.php`:
   - add `OPENAI_API_KEY`
   - change `ADMIN_KEY`

2. upload all files to your server

3. create `data/` folder with write permissions

4. done

## structure

```
index.html, about.html, privacy.html, terms.html
api.php, sw.js, manifest.json, sitemap.xml, robots.txt
assets/css/
assets/images/og-image.png
scripts/app.js
includes/config.php, db.php, openai.php
data/ (sqlite db, auto-created)
```

## stack

- php 7.4+
- sqlite (zero config)
- openai gpt-4o-mini
- vanilla html/css/js

## analytics

access at: `/api.php?action=stats&key=YOUR_ADMIN_KEY`

## license

mit - do whatever
