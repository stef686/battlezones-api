# Uploads live on a private Cloudflare R2 bucket and are reached by signed URL

Every user upload — Gallery Photos and their thumbnails, Event Banners, Event Documents, Event
Update attachments — is written to a single **uploads disk** rather than to a disk named at the
call site. In production that disk is a private Cloudflare R2 bucket; in development and the test
suite it is `uploads_local`, a local disk served by Laravel. Nothing in the bucket is readable
without a signed URL that expires. `App\Services\UploadStorage` is the only thing that knows any of
this: it hands out the disk and it mints the URLs.

## Why

- **The origin should not be serving image bytes.** Photos and Banners are the bulk of what this
  app transfers over venue wifi, and R2 has no egress charge.
- **Private, not public.** A Gallery Photo of a named Player at a named Event is not something to
  leave permanently fetchable by anyone who ever saw the URL, and R2's public bucket options
  (`r2.dev`, a custom domain) offer no way to take one back short of deleting the file.
- **One disk name, in config.** Twelve call sites each naming `'public'` meant twelve places to
  edit and one to forget. `filesystems.uploads` is the switch; nothing else names a disk.

## Consequences

- **URLs expire, so responses containing them cannot be cached indefinitely.** `UPLOADS_URL_TTL`
  (minutes, default 60) sets the lifetime. Clients must not persist a Photo or Banner URL beyond
  the response that carried it.
- **A signed URL is snapped to a fixed window rather than counted from the instant it is minted.**
  Two reads a second apart would otherwise produce two different URLs for the same file, which
  makes identical responses differ byte-for-byte and defeats every cache in front of them. A URL is
  therefore good for at least one whole TTL and at most two.
- **Development does not get permanent URLs either.** `uploads_local` is deliberately not the
  `public` disk: files sit outside the web root and are reached through Laravel's signed serve
  route, so a link that would not survive production does not work locally. Its URI is `/uploads`,
  not `/storage`, because Laravel refuses to serve two disks from the same URI.
- **Nothing already on the `public` disk was moved.** Records written before this change point at
  files that the uploads disk does not have. Anything predating the switch must be re-uploaded, or
  copied across by hand.
- **`league/flysystem-aws-s3-v3` is now a dependency.** R2 speaks S3; there is no other adapter.
  Laravel special-cases `r2.cloudflarestorage.com` endpoints by not sending ACLs, which R2 rejects,
  so per-file visibility is a bucket-level concern and not something to set per upload.
