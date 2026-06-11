"use client";

import { useState, useEffect } from "react";
import { getSettings, GeneralSettings } from "@/lib/api";

let cachedSettings: GeneralSettings | null = null;
let settingsPromise: Promise<GeneralSettings> | null = null;

export function useSettings() {
  const [settings, setSettings] = useState<GeneralSettings | null>(cachedSettings);
  const [loading, setLoading] = useState(!cachedSettings);

  useEffect(() => {
    if (cachedSettings) {
      setSettings(cachedSettings);
      setLoading(false);
      return;
    }

    if (!settingsPromise) {
      settingsPromise = getSettings()
        .then((data) => {
          cachedSettings = data;
          return data;
        });
    }

    settingsPromise
      .then((data) => {
        setSettings(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Error loading general settings:", err);
        setLoading(false);
      });
  }, []);

  return { settings, loading };
}
