# Bayangan Archive update — 18 October 2026

## Changes
- Added a pottery type/category field to Admin > Pottery Records and grouped public archive cards by type, with category quick links and a type filter.
- Added standardized tutorial topic choices for clay/soil preparation, tools/materials, shaping, drying, firing, finishing, culture, safety, and other tutorials. Public tutorials are grouped by topic and include jump links.
- Removed the unused Camera-Based Heritage View section, its camera start/stop JavaScript, camera-only styles, and camera-only event/quest references. Other interactive activities and audio recording controls are preserved.
- Added a safe, additive migration helper that adds `Pots.pot_type` to an existing database only if the column is missing. It does not delete or reset existing records.

## Existing data
Existing pottery records are assigned `Other Pottery` by default after the new column is added. An administrator can edit each record and select the correct category based on verified field information. Existing tutorial categories are retained; choose a standardized topic when editing each tutorial if you want the topics to match the new groups.

## Testing still required in the target XAMPP environment
Open the public Archive and Tutorials pages, add/edit a pottery record and tutorial, check media playback, confirm category links/filtering, and open Interactive Heritage Lab to confirm the remaining activities work. The source files were checked for PHP syntax; database/browser runtime behavior must be confirmed on the actual XAMPP/MySQL setup.
