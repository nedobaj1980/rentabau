/**
 * Rent A Bau - Shared Application Logic & Pricing Engine
 * Category Hierarchy & Scalable Dynamic Architecture
 * Product-Specific Delivery Calculator (Romanshorn TG)
 * Payment Engine (TWINT, Mastercard, Banküberweisung/QR-Rechnung, Barzahlung bei Abholung)
 * ISO & Swiss German Date Normalizer
 */

let CATEGORIES = [
  "Bagger",
  "Fahrzeuge",
  "Kleinmaschinen",
  "Abbruch",
  "Transport & Anhänger"
];

let CATEGORY_SPECS = {
  "Bagger": ["gewicht", "grabtiefe", "breite", "leistung"],
  "Fahrzeuge": ["nutzlast", "muldeninhalt", "breite", "antrieb"],
  "Kleinmaschinen": ["gewicht", "fliehkraft", "arbeitsbreite", "kraftstoff"],
  "Abbruch": ["einsatzgewicht", "oelmenge", "arbeitsdruck", "traegergeraet"],
  "Transport & Anhänger": ["nutzlast", "gesamtgewicht", "ladeflaeche", "kupplung"]
};

let DEFAULT_PAYMENT_CONFIG = {
  bank: {
    enabled: true,
    bankName: "Thurgauer Kantonalbank (TKB)",
    accountHolder: "Rent A Bau AG",
    iban: "CH93 0078 2000 1234 5678 9",
    qrIban: "CH44 3199 9123 4567 8901 2"
  },
  twint: {
    enabled: true,
    phone: "+41 79 123 45 67",
    label: "Rent A Bau TWINT Pay"
  },
  mastercard: {
    enabled: true,
    label: "Mastercard / Visa / Kreditkarte"
  },
  cash: {
    enabled: true,
    label: "Barzahlung bei Abholung / Übergabe",
    allowShortNoticeDays: 3
  }
};

let DEFAULT_DELIVERY_CONFIG = {
  baseLocation: "Romanshorn (TG)",
  baseFee: 40,
  tiers: [
    { maxKm: 15, ratePerKm: 4.00 },
    { maxKm: 40, ratePerKm: 3.00 },
    { maxKm: 100, ratePerKm: 2.20 },
    { maxKm: 999, ratePerKm: 1.80 }
  ],
  transportClasses: {
    "leicht": { name: "Leichttransport (Anhänger / Bus)", multiplier: 1.0 },
    "mittel": { name: "Mitteltransport (3.5t Anhänger / Transporter)", multiplier: 1.4 },
    "schwer": { name: "Schwertransport (LKW / Tieflader)", multiplier: 2.2 }
  }
};

const DEFAULT_MACHINES_LIST = [
  {
    id: "minibagger-18",
    name: "Minibagger 1.8 t",
    category: "Bagger",
    description: "Kompakt, kräftig und ideal für Gartenbau, Leitungsgräben und enge Zufahrten.",
    badge: "Beliebt",
    hourlyPrice: 45,
    maxHoursPerDay: 5,
    weekendPrice: 390,
    deposit: 800,
    status: "online",
    image: "brand/rent-a-bau-hero.png",
    images: ["brand/rent-a-bau-hero.png"],
    transportClass: "mittel",
    loadUnloadFee: 40,
    relatedIds: ["raddumper-15", "ruettelplatte-500"],
    accessories: [
      { name: "Löffelpaket (30cm, 50cm, Grabensohle)", price: 0 },
      { name: "Hydraulischer Abbruchhammer", price: 40 },
      { name: "Maschinen-Transportanhänger 3.5t", price: 50 }
    ],
    specs: {
      gewicht: "1'800 kg",
      grabtiefe: "2.40 m",
      breite: "990 - 1'300 mm",
      leistung: "11.8 kW (16 PS)"
    }
  },
  {
    id: "raddumper-15",
    name: "Raddumper 1.5 t",
    category: "Fahrzeuge",
    description: "Für Aushub, Kies und Materialtransport auf kleinen und mittleren Baustellen.",
    badge: "",
    hourlyPrice: 35,
    maxHoursPerDay: 5,
    weekendPrice: 310,
    deposit: 600,
    status: "online",
    image: "brand/slider-transport.webp",
    images: ["brand/slider-transport.webp"],
    transportClass: "mittel",
    loadUnloadFee: 35,
    relatedIds: ["minibagger-18", "ruettelplatte-500"],
    accessories: [
      { name: "Auffahrrampen Alu 3.5t", price: 20 },
      { name: "Transportanhänger 3.5t", price: 50 }
    ],
    specs: {
      nutzlast: "1'500 kg",
      muldeninhalt: "800 Liter",
      breite: "1'200 mm",
      antrieb: "Allrad 4x4"
    }
  },
  {
    id: "ruettelplatte-500",
    name: "Rüttelplatte 500 kg",
    category: "Kleinmaschinen",
    description: "Leistungsstarke reversierbare Platte für Fundationen, Plätze und Zufahrten.",
    badge: "",
    hourlyPrice: 25,
    maxHoursPerDay: 5,
    weekendPrice: 220,
    deposit: 400,
    status: "online",
    image: "brand/slider-verdichtung.webp",
    images: ["brand/slider-verdichtung.webp"],
    transportClass: "leicht",
    loadUnloadFee: 20,
    relatedIds: ["minibagger-18", "raddumper-15"],
    accessories: [
      { name: "Vulkanmatte für Pflastersteine", price: 15 }
    ],
    specs: {
      gewicht: "480 kg",
      fliehkraft: "60 kN",
      arbeitsbreite: "650 mm",
      kraftstoff: "Diesel"
    }
  }
];

let MACHINES = JSON.parse(JSON.stringify(DEFAULT_MACHINES_LIST));

let SYSTEM_SETTINGS = {
  categories: CATEGORIES,
  categorySpecs: CATEGORY_SPECS,
  paymentConfig: DEFAULT_PAYMENT_CONFIG,
  deliveryConfig: DEFAULT_DELIVERY_CONFIG,
  discounts: [
    { minDays: 3, percent: 10 },
    { minDays: 7, percent: 15 },
    { minDays: 14, percent: 25 }
  ],
  machines: {},
  blockedDates: []
};

// Helper: Normalize Date String or Object to YYYY-MM-DD
function parseToIsoDateString(rawStr) {
  if (!rawStr) return "";
  if (rawStr instanceof Date) {
    if (isNaN(rawStr.getTime())) return "";
    const y = rawStr.getFullYear();
    const m = String(rawStr.getMonth() + 1).padStart(2, '0');
    const day = String(rawStr.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  const str = String(rawStr).trim();
  if (!str) return "";

  if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
  if (/^\d{2}\.\d{2}\.\d{4}$/.test(str)) {
    const parts = str.split(".");
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
  }

  const d = new Date(str);
  if (!isNaN(d.getTime())) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }
  return str;
}

// Fetch Settings
async function fetchSettings() {
  try {
    const res = await fetch("api/settings.php");
    if (res.ok) {
      const data = await res.json();
      SYSTEM_SETTINGS = data;

      if (Array.isArray(data.categories) && data.categories.length > 0) {
        CATEGORIES = data.categories;
      }

      if (data.categorySpecs && typeof data.categorySpecs === "object") {
        CATEGORY_SPECS = data.categorySpecs;
      }

      if (data.paymentConfig && typeof data.paymentConfig === "object") {
        SYSTEM_SETTINGS.paymentConfig = data.paymentConfig;
      }

      if (data.deliveryConfig && typeof data.deliveryConfig === "object") {
        SYSTEM_SETTINGS.deliveryConfig = data.deliveryConfig;
      }

      if (data.machines) {
        const fetchedIds = Object.keys(data.machines);
        
        fetchedIds.forEach(id => {
          const custom = data.machines[id];
          let existing = MACHINES.find(m => m.id === id);

          const defaultForId = DEFAULT_MACHINES_LIST.find(m => m.id === id);
          const fallbackImage = (existing && existing.image) || (defaultForId && defaultForId.image) || "brand/rent-a-bau-hero.png";
          const fallbackImages = (existing && existing.images) || (defaultForId && defaultForId.images) || [fallbackImage];

          const customImages = Array.isArray(custom.images) && custom.images.length > 0 ? 
            custom.images : 
            (custom.image ? [custom.image] : fallbackImages);
          
          const coverImage = custom.image || customImages[0] || fallbackImage;

          if (!existing) {
            existing = {
              id: id,
              name: custom.name || id,
              category: custom.category || "Allgemein",
              description: custom.description || "",
              badge: custom.badge || "",
              hourlyPrice: parseFloat(custom.hourlyPrice) || 30,
              maxHoursPerDay: parseInt(custom.maxHoursPerDay) || 5,
              weekendPrice: parseFloat(custom.weekendPrice) || 200,
              deposit: parseFloat(custom.deposit) || 500,
              status: custom.status || "online",
              image: coverImage,
              images: customImages,
              transportClass: custom.transportClass || "mittel",
              loadUnloadFee: custom.loadUnloadFee !== undefined ? parseFloat(custom.loadUnloadFee) : 35,
              relatedIds: custom.relatedIds || [],
              accessories: custom.accessories || [],
              specs: custom.specs || {}
            };
            MACHINES.push(existing);
          } else {
            if (custom.name) existing.name = custom.name;
            if (custom.category) existing.category = custom.category;
            if (custom.description) existing.description = custom.description;
            if (custom.badge !== undefined) existing.badge = custom.badge;
            if (custom.hourlyPrice) existing.hourlyPrice = parseFloat(custom.hourlyPrice);
            if (custom.maxHoursPerDay) existing.maxHoursPerDay = parseInt(custom.maxHoursPerDay);
            if (custom.weekendPrice) existing.weekendPrice = parseFloat(custom.weekendPrice);
            if (custom.deposit) existing.deposit = parseFloat(custom.deposit);
            if (custom.status) existing.status = custom.status;
            if (custom.transportClass) existing.transportClass = custom.transportClass;
            if (custom.loadUnloadFee !== undefined) existing.loadUnloadFee = parseFloat(custom.loadUnloadFee);
            if (custom.image) existing.image = custom.image;
            if (Array.isArray(custom.images) && custom.images.length > 0) existing.images = custom.images;
            existing.relatedIds = custom.relatedIds || existing.relatedIds || [];
            existing.accessories = custom.accessories || existing.accessories || [];
            if (custom.specs) existing.specs = { ...custom.specs };
          }
        });

        if (fetchedIds.length > 0) {
          MACHINES = MACHINES.filter(m => fetchedIds.includes(m.id));
        }
      }

      if (!MACHINES || MACHINES.length === 0) {
        MACHINES = JSON.parse(JSON.stringify(DEFAULT_MACHINES_LIST));
      }
      return data;
    }
  } catch (e) {
    console.warn("PHP Settings API fallback", e);
  }

  if (!MACHINES || MACHINES.length === 0) {
    MACHINES = JSON.parse(JSON.stringify(DEFAULT_MACHINES_LIST));
  }
  return SYSTEM_SETTINGS;
}

// Save Settings
async function saveSettings(payload) {
  try {
    const res = await fetch("api/settings.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    if (res.ok) {
      const data = await res.json();
      await fetchSettings();
      return data;
    }
  } catch (e) {
    console.warn("PHP Save Settings fallback", e);
  }
  return null;
}

// Delete Machine
async function deleteMachineFromApi(machineId) {
  try {
    const res = await fetch("api/settings.php", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ deleteMachineId: machineId })
    });
    if (res.ok) {
      await fetchSettings();
      return true;
    }
  } catch (e) {
    console.warn("Delete machine API error", e);
  }
  return false;
}

// Helper: Format Currency CHF
function formatCHF(val) {
  const num = parseFloat(val);
  return new Intl.NumberFormat("de-CH", { style: "currency", currency: "CHF", maximumFractionDigits: 0 }).format(isNaN(num) ? 0 : num);
}

// Helper: Check if rental start date is short-term (e.g. within 3 days)
function isShortNoticeRental(startDateStr, maxDays = 3) {
  const isoStr = parseToIsoDateString(startDateStr);
  if (!isoStr) return false;
  const start = new Date(isoStr);
  if (isNaN(start.getTime())) return false;

  const now = new Date();
  now.setHours(0,0,0,0);
  const diffTime = start.getTime() - now.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays <= maxDays;
}

// Calculate Product-Specific & Degressive Tiered Delivery Cost (ab Romanshorn TG)
function calculateDeliveryCost(machine, distanceKm) {
  const km = Math.max(0, parseFloat(distanceKm) || 0);
  const config = SYSTEM_SETTINGS.deliveryConfig || DEFAULT_DELIVERY_CONFIG;
  const baseLocation = config.baseLocation || "Romanshorn (TG)";

  if (km <= 0) {
    return {
      baseLocation: baseLocation,
      baseFee: 0,
      loadUnloadFee: 0,
      kmFee: 0,
      distanceKm: 0,
      multiplier: 1,
      totalDelivery: 0,
      className: "Abholung",
      breakdown: []
    };
  }

  const baseFee = parseFloat(config.baseFee) || 40;
  const loadUnloadFee = (machine && machine.loadUnloadFee !== undefined) ? parseFloat(machine.loadUnloadFee) : 35;
  
  let tiers = config.tiers || DEFAULT_DELIVERY_CONFIG.tiers;
  tiers = [...tiers].sort((a, b) => a.maxKm - b.maxKm);

  let remainingKm = km;
  let previousMaxKm = 0;
  let rawKmCost = 0;
  const breakdown = [];

  for (let i = 0; i < tiers.length; i++) {
    const tier = tiers[i];
    const tierCapacity = tier.maxKm - previousMaxKm;
    const kmInThisTier = Math.min(remainingKm, tierCapacity);

    if (kmInThisTier > 0) {
      const tierCost = kmInThisTier * parseFloat(tier.ratePerKm);
      rawKmCost += tierCost;
      breakdown.push({
        fromKm: previousMaxKm,
        toKm: previousMaxKm + kmInThisTier,
        kmCount: kmInThisTier,
        ratePerKm: parseFloat(tier.ratePerKm),
        cost: tierCost
      });
      remainingKm -= kmInThisTier;
    }

    previousMaxKm = tier.maxKm;
    if (remainingKm <= 0) break;
  }

  const tClasses = config.transportClasses || DEFAULT_DELIVERY_CONFIG.transportClasses;
  const machineClassKey = (machine && machine.transportClass) ? machine.transportClass : "mittel";
  const classInfo = tClasses[machineClassKey] || { name: "Mitteltransport", multiplier: 1.4 };
  const multiplier = parseFloat(classInfo.multiplier) || 1.0;

  const kmAndBaseSubtotal = Math.round((baseFee + rawKmCost) * multiplier);
  const finalDeliveryTotal = loadUnloadFee + kmAndBaseSubtotal;

  return {
    baseLocation: baseLocation,
    distanceKm: km,
    loadUnloadFee: loadUnloadFee,
    baseFee: baseFee,
    rawKmCost: rawKmCost,
    kmAndBaseSubtotal: kmAndBaseSubtotal,
    multiplier: multiplier,
    classKey: machineClassKey,
    className: classInfo.name,
    totalDelivery: finalDeliveryTotal,
    breakdown: breakdown
  };
}

// Calculate Days with Saturday & Sunday FREE Rule
function calculateDays(startDateStr, endDateStr) {
  const startIso = parseToIsoDateString(startDateStr);
  const endIso = parseToIsoDateString(endDateStr);

  if (!startIso || !endIso) return { totalCalendarDays: 1, billableDays: 1, freeWeekendDays: 0 };
  
  const start = new Date(startIso);
  const end = new Date(endIso);
  if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) {
    return { totalCalendarDays: 1, billableDays: 1, freeWeekendDays: 0 };
  }

  let totalCalendarDays = 0;
  let billableDays = 0;
  let freeWeekendDays = 0;

  let curr = new Date(start);
  while (curr <= end) {
    totalCalendarDays++;
    const dayOfWeek = curr.getDay();
    if (dayOfWeek === 0 || dayOfWeek === 6) {
      freeWeekendDays++;
    } else {
      billableDays++;
    }
    curr.setDate(curr.getDate() + 1);
  }

  if (billableDays === 0 && totalCalendarDays > 0) {
    billableDays = 1;
    freeWeekendDays = totalCalendarDays - 1;
  }

  return { totalCalendarDays, billableDays, freeWeekendDays };
}

// Dynamic Long-Term Discount Percentage Engine
function getDiscountPercent(days) {
  let tiers = SYSTEM_SETTINGS.discounts || [
    { minDays: 3, percent: 10 },
    { minDays: 7, percent: 15 },
    { minDays: 14, percent: 25 }
  ];

  if (!Array.isArray(tiers)) {
    tiers = [
      { minDays: 3, percent: tiers.tier3 || 10 },
      { minDays: 7, percent: tiers.tier7 || 15 },
      { minDays: 14, percent: tiers.tier14 || 25 }
    ];
  }

  const sorted = [...tiers].sort((a, b) => b.minDays - a.minDays);
  const matched = sorted.find(t => days >= t.minDays);

  return matched ? matched.percent : 0;
}

// Multi-Day & Multi-Hour Rental Calculation Engine
function calculateRentalPrice(machine, options) {
  if (!machine) {
    return {
      type: "tag", totalCalendarDays: 1, days: 1, billableDays: 1, freeWeekendDays: 0,
      hoursPerDay: 5, dayPrice: 225, grossTotal: 225, discountPercent: 0, discountAmount: 0, netTotal: 225, deposit: 500
    };
  }

  const { type, startDate, endDate, hoursPerDay } = options;
  const { totalCalendarDays, billableDays, freeWeekendDays } = calculateDays(startDate, endDate);

  const hourlyPrice = parseFloat(machine.hourlyPrice) || 30;
  const maxHoursPerDay = parseInt(machine.maxHoursPerDay) || 5;
  const deposit = parseFloat(machine.deposit) || 500;

  if (type === "stunde") {
    const hrsPerDay = Math.max(1, parseInt(hoursPerDay) || 1);
    const chargedHrsPerDay = Math.min(hrsPerDay, maxHoursPerDay);
    const isCappedAtDay = hrsPerDay >= maxHoursPerDay;
    
    const dailyRate = chargedHrsPerDay * hourlyPrice;
    const grossTotal = billableDays * dailyRate;

    const discountPercent = getDiscountPercent(billableDays);
    const discountAmount = (grossTotal * discountPercent) / 100;
    const netTotal = grossTotal - discountAmount;

    return {
      type: "stunde",
      totalCalendarDays: totalCalendarDays,
      days: billableDays,
      billableDays: billableDays,
      freeWeekendDays: freeWeekendDays,
      hoursPerDay: hrsPerDay,
      chargedHrsPerDay: chargedHrsPerDay,
      totalHours: billableDays * hrsPerDay,
      hourlyPrice: hourlyPrice,
      isCappedAtDay: isCappedAtDay,
      dailyRate: dailyRate,
      grossTotal: grossTotal,
      discountPercent: discountPercent,
      discountAmount: discountAmount,
      netTotal: netTotal,
      deposit: deposit
    };
  }

  const dayFlatPrice = hourlyPrice * maxHoursPerDay;
  const grossTotal = billableDays * dayFlatPrice;
  const discountPercent = getDiscountPercent(billableDays);
  const discountAmount = (grossTotal * discountPercent) / 100;
  const netTotal = grossTotal - discountAmount;

  return {
    type: "tag",
    totalCalendarDays: totalCalendarDays,
    days: billableDays,
    billableDays: billableDays,
    freeWeekendDays: freeWeekendDays,
    hoursPerDay: maxHoursPerDay,
    dayPrice: dayFlatPrice,
    grossTotal: grossTotal,
    discountPercent: discountPercent,
    discountAmount: discountAmount,
    netTotal: netTotal,
    deposit: deposit
  };
}

// Fetch Reservations
async function fetchReservations(options = {}) {
  try {
    const params = new URLSearchParams();
    Object.entries(options || {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null && String(value) !== "") params.set(key, String(value));
    });
    const query = params.toString();
    const res = await fetch(`api/reservations.php${query ? `?${query}` : ""}`);
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data)) return data;
      if (data.reservations) return data.reservations;
    }
  } catch (e) {
    console.warn("PHP API fetch fallback", e);
  }
  const local = localStorage.getItem("rentabau_reservations");
  return local ? JSON.parse(local) : [];
}

// Save Reservation
async function saveReservation(payload) {
  try {
    const res = await fetch("api/reservations.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    if (res.ok) {
      const data = await res.json();
      return data;
    }
  } catch (e) {
    console.warn("PHP API save fallback", e);
  }
  
  const current = await fetchReservations();
  const newReservation = {
    id: Date.now(),
    ...payload,
    status: "gebucht",
    createdAt: new Date().toISOString()
  };
  current.unshift(newReservation);
  localStorage.setItem("rentabau_reservations", JSON.stringify(current));
  return { success: true, reservation: newReservation };
}

// Admin reservation update
async function updateReservationDetails(id, details) {
  try {
    const res = await fetch("api/reservations.php", {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, ...details })
    });
    if (res.ok) return await res.json();
    const error = await res.json().catch(() => ({}));
    throw new Error(error.error || "Speichern fehlgeschlagen");
  } catch (e) {
    console.error("Reservation update", e);
    throw e;
  }
}

async function updateReservationStatus(id, newStatus) {
  return updateReservationDetails(id, { status: newStatus });
}

document.addEventListener("DOMContentLoaded", async () => {
  await fetchSettings();

  const currentPath = window.location.pathname;
  document.querySelectorAll("nav a").forEach(link => {
    if (link.getAttribute("href") && currentPath.includes(link.getAttribute("href"))) {
      link.classList.add("active");
    }
  });
});

// ============================================================
// Global Fullscreen Lightbox Image Viewer
// ============================================================
let activeLightboxImages = [];
let activeLightboxIndex = 0;
let activeLightboxTitle = "";

function createLightboxDOM() {
  if (document.getElementById("rentabau-lightbox-modal")) return;

  const modal = document.createElement("div");
  modal.id = "rentabau-lightbox-modal";
  modal.className = "rentabau-lightbox";
  modal.setAttribute("role", "dialog");
  modal.setAttribute("aria-modal", "true");
  modal.innerHTML = `
    <div class="lightbox-backdrop" onclick="closeImageLightbox()"></div>
    <div class="lightbox-content">
      <button type="button" class="lightbox-close" onclick="closeImageLightbox()" aria-label="Schliessen">✕</button>
      <button type="button" class="lightbox-arrow lightbox-prev" onclick="changeLightboxSlide(-1)" aria-label="Vorheriges Bild">‹</button>
      <div class="lightbox-img-wrapper" onclick="event.stopPropagation()">
        <img id="lightbox-current-img" src="" alt="Vollbild Ansicht">
      </div>
      <button type="button" class="lightbox-arrow lightbox-next" onclick="changeLightboxSlide(1)" aria-label="Nächstes Bild">›</button>
      <div class="lightbox-footer">
        <span id="lightbox-title" class="lightbox-caption"></span>
        <span id="lightbox-counter" class="lightbox-counter"></span>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  document.addEventListener("keydown", (e) => {
    const lb = document.getElementById("rentabau-lightbox-modal");
    if (!lb || lb.style.display !== "flex") return;
    if (e.key === "Escape") closeImageLightbox();
    if (e.key === "ArrowLeft") changeLightboxSlide(-1);
    if (e.key === "ArrowRight") changeLightboxSlide(1);
  });
}

function openImageLightbox(imagesInput, startIndex = 0, title = "") {
  createLightboxDOM();
  let imgList = [];
  if (Array.isArray(imagesInput)) {
    imgList = imagesInput.filter(Boolean);
  } else if (typeof imagesInput === "string") {
    imgList = imagesInput.includes("|") ? imagesInput.split("|").filter(Boolean) : [imagesInput];
  }

  if (imgList.length === 0) {
    imgList = ["brand/rent-a-bau-hero.png"];
  }

  activeLightboxImages = imgList;
  activeLightboxIndex = Math.max(0, Math.min(startIndex, imgList.length - 1));
  activeLightboxTitle = title || "";

  updateLightboxView();
  const modal = document.getElementById("rentabau-lightbox-modal");
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";
}

function updateLightboxView() {
  const imgEl = document.getElementById("lightbox-current-img");
  const titleEl = document.getElementById("lightbox-title");
  const counterEl = document.getElementById("lightbox-counter");
  const prevBtn = document.querySelector(".lightbox-prev");
  const nextBtn = document.querySelector(".lightbox-next");

  if (imgEl && activeLightboxImages[activeLightboxIndex]) {
    imgEl.src = activeLightboxImages[activeLightboxIndex];
  }
  if (titleEl) {
    titleEl.innerText = activeLightboxTitle;
  }
  if (counterEl) {
    counterEl.innerText = activeLightboxImages.length > 1 ? `${activeLightboxIndex + 1} / ${activeLightboxImages.length}` : "";
  }
  if (prevBtn) {
    prevBtn.style.display = activeLightboxImages.length > 1 ? "flex" : "none";
  }
  if (nextBtn) {
    nextBtn.style.display = activeLightboxImages.length > 1 ? "flex" : "none";
  }
}

function changeLightboxSlide(delta) {
  if (!activeLightboxImages || activeLightboxImages.length <= 1) return;
  activeLightboxIndex = (activeLightboxIndex + delta + activeLightboxImages.length) % activeLightboxImages.length;
  updateLightboxView();
}

function closeImageLightbox() {
  const modal = document.getElementById("rentabau-lightbox-modal");
  if (modal) {
    modal.style.display = "none";
  }
  document.body.style.overflow = "";
}
