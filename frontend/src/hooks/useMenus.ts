"use client";

import { useState, useEffect } from "react";
import { getMenus, ApiMenus } from "@/lib/api";

let cachedMenus: ApiMenus | null = null;
let menusPromise: Promise<ApiMenus> | null = null;

export function useMenus() {
  const [menus, setMenus] = useState<ApiMenus | null>(cachedMenus);
  const [loading, setLoading] = useState(!cachedMenus);

  useEffect(() => {
    if (cachedMenus) {
      setMenus(cachedMenus);
      setLoading(false);
      return;
    }

    if (!menusPromise) {
      menusPromise = getMenus()
        .then((data) => {
          cachedMenus = data;
          return data;
        });
    }

    menusPromise
      .then((data) => {
        setMenus(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Error loading menus:", err);
        setLoading(false);
      });
  }, []);

  return { menus, loading };
}
