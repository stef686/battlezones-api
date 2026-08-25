# Banners are normalised on upload and the original is discarded

An Event's Banner is uploaded by an Organiser as whatever their phone or design tool produced, and
is displayed full-width at 160px tall on every Event screen — an aspect ratio that differs on every
device. We scale-and-crop on upload to a fixed 1600×534 (3:1) WebP plus an 800×267 variant, store
only those, and **discard the uploaded original**.

## Why

- **The venue's wifi is the worst network this app will ever see.** Serving a 6MB phone photo on
  every Event screen is the failure mode we are buying our way out of; WebP at two fixed sizes is
  roughly half the bytes of equivalent JPEG.
- **The crop becomes predictable**, so what an Organiser sees when they upload is what Players see.
- Per-Event storage is capped at a known size.

## Consequences

- **Re-cropping later is impossible without re-uploading.** There is no original to go back to.
  This is the price of the above and was accepted knowingly. A crop UI, if one is ever built, must
  either keep originals from that point on or accept that older Banners cannot be re-framed.
- **The crop is biased toward the top of the image, not centred.** The Event's game system, name
  and dates are overlaid at the *bottom* of the header, so a centre crop would put a logo or a
  subject's face exactly where the text sits. Banners are typically composed with the interesting
  part in the upper two-thirds.
- **SVG is refused, and `mimes:jpeg,png,webp` is used rather than Laravel's `image` rule.** An SVG
  is a document that can carry script, and Banners are served from the same public disk as
  everything else. This is a security decision, not a formatting preference — do not widen it.
  Animated GIF is refused too: it cannot survive the crop meaningfully, and motion behind the Event
  name fights reading it.
- **Uploads below 1200×400 are rejected rather than upscaled**, because the normalisation would
  otherwise produce a soft header on exactly the screens Players use. This does reject a small
  square club logo, which is plausibly the first thing an Organiser tries; the alternative —
  letterboxing it against the flat background — would mean a second layout mode in the header, and
  the header deliberately has one.
- Replacing a Banner deletes the previous files before writing new ones, following
  `PhotoStorageService::replace()`. UUID filenames mean there is no CDN cache-busting problem.
- A Banner is not a `Photo`: it is not in the Gallery, not attributed to a Player, and not
  reactable. It does not use `PhotoStorageService`, only its shape.
