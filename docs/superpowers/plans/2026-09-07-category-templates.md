# Reusable category templates

User requested easier category entry and reuse across machines. Extend current category criteria with labels, units, input types, choices, optional defaults and help text. Keep stored spec keys stable. Retain the existing string-key list for compatibility with public pages.

1. Test legacy conversion, additive merging and preservation of existing values.
2. Implement a validated, CSRF-protected admin endpoint that updates the existing settings file under an exclusive lock. Preserve catalogue/payment data. Optional bulk application fills only empty fields in the current category.
3. Replace category editor with reusable field cards, presets, copy-from-category and a machine-form preview. Add friendly typed fields to new and existing machine editors without erasing values on category changes.
4. Verify save/reload, category defaults, form reuse, validation, access protection and existing public pages. Back up source and settings first. No deployment or unrelated price changes.
