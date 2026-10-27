<?php
// Global Rent A Bau Fullscreen Lightbox Component
?>
<div id="rentabau-lightbox-modal" class="rentabau-lightbox" style="display: none;" role="dialog" aria-modal="true" aria-label="Bildergalerie Vollbild">
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
</div>

<style id="rentabau-lightbox-styles">
.rentabau-lightbox {
  position: fixed !important;
  inset: 0 !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  z-index: 9999999 !important;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box !important;
  padding: 0 !important;
  margin: 0 !important;
}

.lightbox-backdrop {
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  background: rgba(0, 0, 0, 0.92) !important;
  backdrop-filter: blur(10px) !important;
  -webkit-backdrop-filter: blur(10px) !important;
  cursor: pointer !important;
}

.lightbox-content {
  position: relative !important;
  z-index: 1000 !important;
  max-width: 96vw !important;
  max-height: 94vh !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  pointer-events: none !important;
}

.lightbox-content > * {
  pointer-events: auto !important;
}

.lightbox-img-wrapper {
  max-width: 90vw !important;
  max-height: 82vh !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  box-shadow: 0 25px 80px rgba(0,0,0,0.85) !important;
  background: #000 !important;
}

#lightbox-current-img {
  max-width: 90vw !important;
  max-height: 80vh !important;
  width: auto !important;
  height: auto !important;
  object-fit: contain !important;
  display: block !important;
  user-select: none !important;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.lightbox-close {
  position: fixed !important;
  top: 20px !important;
  right: 24px !important;
  background: rgba(255, 255, 255, 0.2) !important;
  color: #fff !important;
  border: 1px solid rgba(255, 255, 255, 0.45) !important;
  width: 48px !important;
  height: 48px !important;
  border-radius: 50% !important;
  font-size: 24px !important;
  font-weight: 900 !important;
  line-height: 1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  z-index: 2000 !important;
}

.lightbox-close:hover {
  background: #ff5500 !important;
  border-color: #ff5500 !important;
  transform: scale(1.1) !important;
}

.lightbox-arrow {
  position: fixed !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  background: rgba(255, 255, 255, 0.2) !important;
  color: #fff !important;
  border: 1px solid rgba(255, 255, 255, 0.45) !important;
  width: 56px !important;
  height: 56px !important;
  border-radius: 50% !important;
  font-size: 38px !important;
  line-height: 1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  z-index: 2000 !important;
}

.lightbox-prev { left: 24px !important; }
.lightbox-next { right: 24px !important; }

.lightbox-arrow:hover {
  background: #ff5500 !important;
  border-color: #ff5500 !important;
  transform: translateY(-50%) scale(1.1) !important;
}

.lightbox-footer {
  margin-top: 14px !important;
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  width: 100% !important;
  max-width: 90vw !important;
  color: #fff !important;
  font-size: 15px !important;
  text-shadow: 0 1px 4px rgba(0,0,0,0.8) !important;
}

.lightbox-caption {
  font-weight: 800 !important;
  font-size: 16px !important;
}

.lightbox-counter {
  font-weight: 700 !important;
  color: #fdba74 !important;
  background: rgba(0,0,0,0.6) !important;
  padding: 4px 12px !important;
  border-radius: 12px !important;
}

.has-lightbox-zoom {
  cursor: zoom-in !important;
}

.zoom-hint-tag {
  position: absolute !important;
  bottom: 8px !important;
  right: 8px !important;
  background: rgba(0, 0, 0, 0.75) !important;
  backdrop-filter: blur(4px) !important;
  -webkit-backdrop-filter: blur(4px) !important;
  color: #fff !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  padding: 4px 8px !important;
  border-radius: 4px !important;
  pointer-events: none !important;
  display: flex !important;
  align-items: center !important;
  gap: 4px !important;
  z-index: 5 !important;
  box-shadow: 0 2px 6px rgba(0,0,0,0.25) !important;
}

@media (max-width: 768px) {
  .lightbox-arrow {
    width: 44px !important;
    height: 44px !important;
    font-size: 26px !important;
  }
  .lightbox-prev { left: 10px !important; }
  .lightbox-next { right: 10px !important; }
  .lightbox-close { top: 12px !important; right: 12px !important; width: 42px !important; height: 42px !important; font-size: 20px !important; }
}
</style>

<script>
(function() {
  window.activeLightboxImages = [];
  window.activeLightboxIndex = 0;
  window.activeLightboxTitle = "";

  window.openImageLightbox = function(imagesInput, startIndex, title) {
    startIndex = startIndex || 0;
    title = title || "";

    var imgList = [];
    if (Array.isArray(imagesInput)) {
      imgList = imagesInput.filter(Boolean);
    } else if (typeof imagesInput === "string") {
      imgList = imagesInput.indexOf("|") !== -1 ? imagesInput.split("|").filter(Boolean) : [imagesInput];
    }

    if (imgList.length === 0) {
      imgList = ["brand/rent-a-bau-hero.png"];
    }

    window.activeLightboxImages = imgList;
    window.activeLightboxIndex = Math.max(0, Math.min(startIndex, imgList.length - 1));
    window.activeLightboxTitle = title || "";

    window.updateLightboxView();

    var modal = document.getElementById("rentabau-lightbox-modal");
    if (modal) {
      modal.style.setProperty("display", "flex", "important");
      document.body.style.overflow = "hidden";
    }
  };

  window.updateLightboxView = function() {
    var imgEl = document.getElementById("lightbox-current-img");
    var titleEl = document.getElementById("lightbox-title");
    var counterEl = document.getElementById("lightbox-counter");
    var prevBtn = document.querySelector(".lightbox-prev");
    var nextBtn = document.querySelector(".lightbox-next");

    if (imgEl && window.activeLightboxImages[window.activeLightboxIndex]) {
      imgEl.src = window.activeLightboxImages[window.activeLightboxIndex];
    }
    if (titleEl) {
      titleEl.innerText = window.activeLightboxTitle || "";
    }
    if (counterEl) {
      counterEl.innerText = window.activeLightboxImages.length > 1 
        ? (window.activeLightboxIndex + 1) + " / " + window.activeLightboxImages.length 
        : "";
    }
    if (prevBtn) {
      prevBtn.style.display = window.activeLightboxImages.length > 1 ? "flex" : "none";
    }
    if (nextBtn) {
      nextBtn.style.display = window.activeLightboxImages.length > 1 ? "flex" : "none";
    }
  };

  window.changeLightboxSlide = function(delta) {
    if (!window.activeLightboxImages || window.activeLightboxImages.length <= 1) return;
    window.activeLightboxIndex = (window.activeLightboxIndex + delta + window.activeLightboxImages.length) % window.activeLightboxImages.length;
    window.updateLightboxView();
  };

  window.closeImageLightbox = function() {
    var modal = document.getElementById("rentabau-lightbox-modal");
    if (modal) {
      modal.style.setProperty("display", "none", "important");
    }
    document.body.style.overflow = "";
  };

  window.openMachineLightboxById = function(machineId) {
    if (typeof MACHINES !== "undefined" && Array.isArray(MACHINES)) {
      var m = MACHINES.find(function(item) { return item.id === machineId; });
      if (m) {
        var imgs = (m.images && m.images.length > 0) ? m.images : [m.image || "brand/rent-a-bau-hero.png"];
        window.openImageLightbox(imgs, 0, m.name);
        return;
      }
    }
    window.openImageLightbox(["brand/rent-a-bau-hero.png"], 0, "");
  };

  // Keyboard Navigation
  document.addEventListener("keydown", function(e) {
    var lb = document.getElementById("rentabau-lightbox-modal");
    if (!lb || lb.style.display === "none") return;
    if (e.key === "Escape") window.closeImageLightbox();
    if (e.key === "ArrowLeft") window.changeLightboxSlide(-1);
    if (e.key === "ArrowRight") window.changeLightboxSlide(1);
  });

  // Global Click Event Delegation
  document.addEventListener("click", function(e) {
    var target = e.target;
    if (!target) return;
    if (target.closest && target.closest("#rentabau-lightbox-modal")) return;

    // Check if clicked inside machine-image on home or catalogue
    var machineImgContainer = target.closest ? target.closest(".machine-image, .gallery-main-wrapper") : null;
    if (machineImgContainer && (!target.closest || !target.closest("a, button"))) {
      var card = machineImgContainer.closest(".machine-card, .portal-grid, .card");
      var title = card ? (card.querySelector("h3, h1, #product-title")?.innerText?.trim() || "") : "";
      var img = machineImgContainer.querySelector("img");
      if (img && img.src) {
        // Try detail link machine id
        var detailLink = card ? card.querySelector("a[href*='maschine-detail.php']")?.getAttribute("href") : "";
        var mId = "";
        if (detailLink) {
          var match = detailLink.match(/id=([^&]+)/);
          if (match) mId = decodeURIComponent(match[1]);
        }
        if (!mId && typeof currentMachine !== "undefined" && currentMachine && currentMachine.id) {
          mId = currentMachine.id;
        }
        if (mId && typeof MACHINES !== "undefined" && Array.isArray(MACHINES)) {
          var m = MACHINES.find(function(item) { 
            return item.id === mId || item.id === mId.replace(/-+$/, ""); 
          });
          if (m) {
            var imgs = (m.images && m.images.length > 0) ? m.images : [m.image || img.src];
            var startIdx = imgs.indexOf(img.getAttribute("src"));
            if (startIdx === -1) startIdx = 0;
            window.openImageLightbox(imgs, startIdx, m.name);
            e.preventDefault();
            e.stopPropagation();
            return;
          }
        }
        window.openImageLightbox([img.src], 0, title);
        e.preventDefault();
        e.stopPropagation();
        return;
      }
    }

    // Direct click on thumbnail
    if (target.classList && target.classList.contains("thumb-img")) {
      var mainImg = document.getElementById("main-product-img");
      if (mainImg) mainImg.src = target.src;
    }
  }, true);
})();
</script>
