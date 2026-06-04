# Disabled workflows

GitHub Actions only runs workflows from `.github/workflows/`. Files here are kept for reference and are not triggered.

## Intranet deploy (not in use)

No intranet branches or secrets are configured for this deployment. See `GAMEPLAN.md`.

To re-enable later:

1. Move the workflow file back to `.github/workflows/`.
2. Configure the `*_STAGE_INTRA` or `*_PROD_INTRA` secrets.
3. Create the matching branch (`stage-intranet`, `production-intranet`, etc.).
