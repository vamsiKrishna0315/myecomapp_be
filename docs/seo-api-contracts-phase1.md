# SEO API Contracts: Phase 1

## Product canonical strategy

- Customer API entity route remains `GET /api/v1/customer/products/{id}` in Phase 1.
- Frontend product page route remains `/product/{id}` in Phase 1.
- Normalized SEO payloads should expose `seo.canonical` using the current customer-facing product detail path when no explicit canonical override is stored.
- Stored `meta_tags.canonical_url` may still override that default for exceptional cases.

## Category slug strategy

- Category payloads now expose a computed `slug`.
- The slug is derived from `category_type`, falling back to `category_name`.
- Slug generation matches the current frontend logic: lowercase plus hyphenated whitespace via Laravel `Str::slug(...)`.
- Frontend routes remain `/category/{slug}` in Phase 1; no route rewrites are introduced here.

## Backward compatibility

- Existing category and product fields remain unchanged.
- New normalized SEO data is additive through `seo`.
- Existing `meta_tags` rows continue to expose raw fields, with additive `seo` and a backward-compatible `show_live` alias derived from `status`.
