import { useEffect, useState, type Dispatch, type SetStateAction } from 'react';
import { getCookie } from './LocalStorage.ts';

const CACHE_PREFIX = 'classmanager:data:v1:';
const DEFAULT_MAX_AGE = 24 * 60 * 60 * 1000;

type CacheEnvelope<T> = {
    expiresAt: number;
    value: T;
};

function hash(value: string): string {
    let result = 2166136261;

    for (let index = 0; index < value.length; index++) {
        result ^= value.charCodeAt(index);
        result = Math.imul(result, 16777619);
    }

    return (result >>> 0).toString(36);
}

function currentScope(): string {
    const token = getCookie('token');
    return token ? hash(token) : 'anonymous';
}

function storageKey(key: string): string {
    return `${CACHE_PREFIX}${currentScope()}:${key}`;
}

export function readCachedData<T>(key: string): T | undefined {
    try {
        const rawValue = window.localStorage.getItem(storageKey(key));
        if (!rawValue) return undefined;

        const cached = JSON.parse(rawValue) as CacheEnvelope<T>;
        if (!cached || cached.expiresAt <= Date.now()) {
            window.localStorage.removeItem(storageKey(key));
            return undefined;
        }

        return cached.value;
    } catch {
        return undefined;
    }
}

export function writeCachedData<T>(key: string, value: T, maxAge = DEFAULT_MAX_AGE): void {
    try {
        const cached: CacheEnvelope<T> = {
            expiresAt: Date.now() + maxAge,
            value,
        };

        window.localStorage.setItem(storageKey(key), JSON.stringify(cached));
    } catch {
        // localStorage may be disabled or full. Network data remains the source of truth.
    }
}

export function clearCurrentUserDataCache(): void {
    try {
        const scopedPrefix = `${CACHE_PREFIX}${currentScope()}:`;
        const keysToRemove: string[] = [];

        for (let index = 0; index < window.localStorage.length; index++) {
            const key = window.localStorage.key(index);
            if (key?.startsWith(scopedPrefix)) keysToRemove.push(key);
        }

        keysToRemove.forEach((key) => window.localStorage.removeItem(key));
    } catch {
        // Clearing cache is best effort and must never block logout.
    }
}

export function useCachedState<T>(
    key: string,
    initialValue: T | (() => T),
    maxAge = DEFAULT_MAX_AGE,
): readonly [T, Dispatch<SetStateAction<T>>, boolean] {
    const [initialCache] = useState(() => {
        const cachedValue = readCachedData<T>(key);
        return {
            hasValue: cachedValue !== undefined,
            value: cachedValue,
        };
    });
    const [value, setValue] = useState<T>(() => {
        if (initialCache.hasValue) return initialCache.value as T;
        return typeof initialValue === 'function'
            ? (initialValue as () => T)()
            : initialValue;
    });

    useEffect(() => {
        writeCachedData(key, value, maxAge);
    }, [key, maxAge, value]);

    return [value, setValue, initialCache.hasValue] as const;
}
