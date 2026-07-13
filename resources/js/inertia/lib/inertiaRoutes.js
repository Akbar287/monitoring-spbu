const regexCache = new Map();

const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

export const localUrl = (href) => {
    if (typeof window === 'undefined' || !href) return null;

    const raw = String(href).trim();
    if (!raw || raw.startsWith('#') || raw.startsWith('javascript:')) return null;

    try {
        const url = new URL(raw, window.location.origin);
        return url.origin === window.location.origin ? url : null;
    } catch {
        return null;
    }
};

const normalizePath = (path) => path.replace(/\/+$/, '') || '/';

const routeUri = (route) => (typeof route === 'string' ? route : route?.uri);
const routeIsInertia = (route) => (typeof route === 'string' ? true : !!route?.inertia);

const templateToRegex = (template) => {
    const normalized = normalizePath('/' + String(template || '').replace(/^\/+/, ''));

    if (regexCache.has(normalized)) return regexCache.get(normalized);
    if (normalized === '/') {
        const rootRegex = /^\/?$/;
        regexCache.set(normalized, rootRegex);
        return rootRegex;
    }

    const segments = normalized.split('/').filter(Boolean);
    let pattern = '^';

    segments.forEach((segment) => {
        if (/^\{[^}]+\?\}$/.test(segment)) {
            pattern += '(?:/[^/]+)?';
            return;
        }

        if (/^\{[^}]+\}$/.test(segment)) {
            pattern += '/[^/]+';
            return;
        }

        pattern += '/' + escapeRegex(segment);
    });

    pattern += '/?$';
    const regex = new RegExp(pattern);
    regexCache.set(normalized, regex);
    return regex;
};

export const isInertiaPath = (pathname, routeTemplates = []) => {
    const path = normalizePath(pathname || '/');
    for (const route of routeTemplates) {
        const uri = routeUri(route);
        if (uri && templateToRegex(uri).test(path)) {
            return routeIsInertia(route);
        }
    }

    return false;
};

export const isInertiaUrl = (href, routeTemplates = []) => {
    const url = localUrl(href);
    return Boolean(url && isInertiaPath(url.pathname, routeTemplates));
};
