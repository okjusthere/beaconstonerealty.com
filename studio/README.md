# Beacon Stone Realty Studio

This directory contains the standalone Sanity Studio for managing Beacon Stone Realty content.

## First phase scope

The Studio currently manages:

- `agent`
- `listing`
- `siteSettings`
- `newsArticle`

## Local development

```bash
cd studio
npm install
npm run dev
```

## Environment

Copy `.env.example` to `.env` and adjust if needed.

The current production Sanity project is:

- `projectId`: `3zakg65j`
- `dataset`: `production`

## Hosted Studio

To deploy to Sanity-hosted Studio:

```bash
cd studio
npm run deploy
```

For CI/CD or self-hosted registration:

```bash
cd studio
npm run deploy:external
```
