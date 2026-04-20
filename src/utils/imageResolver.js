import API_CONFIG from '../config/config';

const API_ORIGIN = String(API_CONFIG.API_BASE_URL || '').replace(/\/api\/v1\/?$/i, '');

const normalizePathValue = (value) => String(value || '').replace(/\\/g, '/').trim();

const PLACEHOLDER_IMAGE_MARKERS = [
  '/images/no-image.svg',
  '/images/yakanlogo.png',
];

export const isPlaceholderImageValue = (value) => {
  const raw = typeof value === 'string'
    ? value
    : (value && typeof value === 'object' ? value.uri : '');
  const normalized = normalizePathValue(raw).toLowerCase();

  if (!normalized) {
    return false;
  }

  return PLACEHOLDER_IMAGE_MARKERS.some((marker) => normalized.includes(marker));
};

export const resolveImageValueToSource = (value) => {
  if (!value) {
    return null;
  }

  if (typeof value === 'object' && typeof value.uri === 'string') {
    if (isPlaceholderImageValue(value.uri)) {
      return null;
    }
    return { uri: normalizePathValue(value.uri) };
  }

  if (typeof value !== 'string') {
    return null;
  }

  const trimmed = normalizePathValue(value);
  if (!trimmed || isPlaceholderImageValue(trimmed)) {
    return null;
  }

  if (trimmed.startsWith('data:image')) {
    return { uri: trimmed };
  }

  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
    return { uri: trimmed };
  }

  if (trimmed.startsWith('//')) {
    return { uri: `https:${trimmed}` };
  }

  const path = trimmed.replace(/^\/+/, '');
  if (!path) {
    return null;
  }

  if (
    path.startsWith('uploads/')
    || path.startsWith('storage/')
    || path.startsWith('chat-image/')
  ) {
    return { uri: `${API_ORIGIN}/${path}` };
  }

  if (
    path.startsWith('variants/')
    || path.startsWith('products/')
    || path.startsWith('product-variants/')
  ) {
    return { uri: `${API_ORIGIN}/uploads/${path}` };
  }

  if (trimmed.startsWith('/')) {
    return { uri: `${API_ORIGIN}${trimmed}` };
  }

  return { uri: `${API_ORIGIN}/uploads/products/${path}` };
};

const extractGalleryImageCandidates = (allImages) => {
  const source = Array.isArray(allImages)
    ? allImages
    : (typeof allImages === 'string'
        ? (() => {
            try {
              const parsed = JSON.parse(allImages);
              return Array.isArray(parsed) ? parsed : [];
            } catch (_) {
              return [];
            }
          })()
        : []);

  return source
    .map((entry) => {
      if (typeof entry === 'string') {
        return entry;
      }

      if (!entry || typeof entry !== 'object') {
        return null;
      }

      return entry.path || entry.url || entry.image || entry.image_url || entry.src || null;
    })
    .filter(Boolean);
};

const collectVariantImageCandidates = (variant) => {
  if (!variant || typeof variant !== 'object') {
    return [];
  }

  return [
    variant.image_url,
    variant.image_src,
    variant.image,
  ];
};

export const pickProductImageValue = (product, preferredVariant = null) => {
  if (!product || typeof product !== 'object') {
    return null;
  }

  const variants = Array.isArray(product.variants) ? product.variants : [];
  const candidates = [];

  candidates.push(...collectVariantImageCandidates(preferredVariant));
  candidates.push(...collectVariantImageCandidates(product.default_variant));

  variants.forEach((variant) => {
    candidates.push(...collectVariantImageCandidates(variant));
  });

  candidates.push(
    product.image_url,
    product.image_src,
    product.image,
    ...extractGalleryImageCandidates(product.all_images),
  );

  const seen = new Set();

  for (const candidate of candidates) {
    if (!candidate) {
      continue;
    }

    const key = typeof candidate === 'string'
      ? normalizePathValue(candidate).toLowerCase()
      : JSON.stringify(candidate);

    if (!key || seen.has(key)) {
      continue;
    }

    seen.add(key);

    if (resolveImageValueToSource(candidate)) {
      return candidate;
    }
  }

  return null;
};

export const getProductImageSource = (product, preferredVariant = null) => {
  const bestValue = pickProductImageValue(product, preferredVariant);
  return resolveImageValueToSource(bestValue);
};
