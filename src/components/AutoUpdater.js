/**
 * AutoUpdater – runs once at app startup.
 * Checks for a new OTA bundle, downloads it, and immediately reboots
 * the JS runtime so users always run the latest version — even after
 * a fresh install (where the cached update was wiped by the uninstall).
 *
 * In Expo Go / development the Updates API is a no-op, so this is safe
 * to leave in for all environments.
 */
import { useEffect } from 'react';
import * as Updates from 'expo-updates';

export default function AutoUpdater() {
  useEffect(() => {
    // Updates are only available in production builds, not in Expo Go
    if (__DEV__) return;

    let cancelled = false;

    (async () => {
      try {
        const check = await Updates.checkForUpdateAsync();
        if (cancelled) return;

        if (check.isAvailable) {
          await Updates.fetchUpdateAsync();
          if (cancelled) return;
          // Reload immediately — user won't even notice because app is
          // still on the loading screen at this point for a fresh install
          await Updates.reloadAsync();
        }
      } catch (_err) {
        // Never crash the app over an update check failure
      }
    })();

    return () => { cancelled = true; };
  }, []);

  return null;
}
