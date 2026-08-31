---
paths:
  - app/Services/EventBannerService.php
  - app/Http/Requests/Events/StoreEventBannerRequest.php
  - app/Services/UploadStorage.php
  - app/Services/AttendeeAvatarService.php
---

# Banners

## A Banner is normalised on upload, cropped from the top, and has no original
`EventBannerService` scales and crops every upload to 1600x534 and 800x267 WebP and discards what was uploaded. There is no going back: re-framing means uploading again. See `docs/adr/0003-banners-are-normalised-on-upload.md`.

The crop is done by hand — scale to cover, then `crop(..., y: 0)` — rather than with `cover()`, which centres. The Event's name and dates are overlaid at the *bottom* of the header, so a centred crop puts a face or a logo exactly where the type goes. Do not "fix" this to a centre crop.

Validation is an explicit `mimes:jpeg,png,webp` allowlist, not Laravel's `image` rule. SVG is a scriptable document served from the same public disk as everything else, so refusing it is a security boundary — do not widen it. Animated GIF is refused too, and uploads under 1200x400 are rejected rather than upscaled.

Two columns (`banner_path`, `banner_small_path`) because both filenames are UUIDs; deriving one from the other would encode a naming convention in string munging. Upload is `POST`/`DELETE /events/{event:slug}/banner`, not a field on the Event PATCH, because PHP does not populate uploaded files for a PATCH body.

## Uploads go through UploadStorage, never a named disk
Every user upload — Photos + thumbnails, Banners, Event Documents, Event Update attachments — uses `App\Services\UploadStorage`: `disk()` to read/write, `url()` to link, `name()` only where an API demands a disk name (Filament `FileUpload`, `UploadedFile::storeAs`). Do not write `Storage::disk('public')` for an upload again; the disk is `config('filesystems.uploads')` — `uploads_local` in dev/tests, `r2` in production.

The bucket is private, so there is no permanent URL: `url()` returns a signed one that expires after `UPLOADS_URL_TTL` minutes. The expiry is snapped to a fixed window, not counted from the call, so the same file signs to the same URL for every caller in that window — identical responses stay byte-identical and cacheable. Do not "fix" that to `now()->addMinutes(...)`.

`uploads_local` is deliberately not the `public` disk: files sit outside the web root and are served through Laravel's signed route at `/uploads`, so a link that would not survive production does not work locally either. Its URI cannot be `/storage` — Laravel refuses two served disks on one URI.

Tests: `Storage::fake(UploadStorage::name())`. See docs/adr/0004-uploads-live-on-a-private-bucket.md.

## A team Avatar is one 256px square, cropped from the centre
`AttendeeAvatarService` covers-crops every upload to a single 256x256 WebP and discards the original, the same bargain as a Banner (ADR 0003) — but one variant, not a pair, because an Avatar is only ever drawn small: an Attendees row, a Standings row, a side of a pairing. Centre crop, not top: nothing is overlaid on it, unlike the Banner whose type sits at the bottom.

Validation mirrors the Banner's — explicit `mimes:jpeg,png,webp` allowlist (SVG is a scriptable document, and refusing it is a security boundary), 8MB, and a 128x128 floor rather than upscaling. Any aspect ratio is accepted; the square is cut on the way in.

Upload is `POST`/`DELETE /events/{event:slug}/attendees/{attendee}/avatar`, not a field on the Attendee PATCH, because PHP does not populate uploaded files for a PATCH body. Authorised with the `update` ability — the team and its Organisers, the same people who name it.

`avatar` is a signed URL from `EventAttendee::avatarUrl()`, serialised in five places: the Attendee list and detail, the Standings, a Round's pairings and a Game. It is drawn at `lg` on the Attendee's own page and on the team's details screen, `xs` in the Standings and a pairing, `sm` in the Attendees list. Null is the common answer, and every screen draws `TeamAvatar`'s initials placeholder rather than a gap.
