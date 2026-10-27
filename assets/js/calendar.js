/**
 * Rent A Bau - Interactive Availability Calendar Component
 * Clean Calendar View with "Nur auf Anfrage möglich" notifications
 */

class AvailabilityCalendar {
  constructor(containerId, options = {}) {
    this.container = document.getElementById(containerId);
    if (!this.container) return;

    this.machine = options.machine;
    this.reservations = options.reservations || [];
    this.blockedDates = options.blockedDates || (SYSTEM_SETTINGS ? SYSTEM_SETTINGS.blockedDates : []) || [];
    this.onSelectRange = options.onSelectRange || function() {};

    this.currentDate = new Date();
    this.selectedStartDate = null;
    this.selectedEndDate = null;

    this.init();
  }

  setReservations(reservations, blockedDates) {
    this.reservations = reservations || [];
    if (blockedDates) this.blockedDates = blockedDates;
    this.render();
  }

  init() {
    this.render();
  }

  // Check if a date string (YYYY-MM-DD) is reserved or manually blocked by admin
  isDateReserved(dateStr) {
    const isReservedByCustomer = this.reservations.some(r => {
      if (r.machineId !== this.machine.id) return false;
      if (r.status === "storniert") return false;
      if (!r.startDate || !r.endDate) return false;
      return dateStr >= r.startDate && dateStr <= r.endDate;
    });

    if (isReservedByCustomer) return true;

    const isBlockedByAdmin = (this.blockedDates || []).some(b => {
      if (b.machineId !== this.machine.id) return false;
      return dateStr >= b.startDate && dateStr <= b.endDate;
    });

    return isBlockedByAdmin;
  }

  formatDateStr(year, month, day) {
    const m = String(month + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return `${year}-${m}-${d}`;
  }

  prevMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
    this.render();
  }

  nextMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
    this.render();
  }

  handleDayClick(dateStr, isReserved, isPast) {
    if (isReserved || isPast) return;

    const clickedDate = new Date(dateStr);
    const dayOfWeek = clickedDate.getDay(); // 0 = So, 6 = Sa

    // Prevent standalone Weekend (Sa/So) as Start Date -> "Nur auf Anfrage möglich"
    if (!this.selectedStartDate || (this.selectedStartDate && this.selectedEndDate)) {
      if (dayOfWeek === 0 || dayOfWeek === 6) {
        alert("Reine Wochenend-Reservationen (nur Samstag/Sonntag) sind nur auf Anfrage möglich. Bitte kontaktiere uns.");
        return;
      }
      this.selectedStartDate = dateStr;
      this.selectedEndDate = null;
    } else if (this.selectedStartDate && !this.selectedEndDate) {
      if (dateStr < this.selectedStartDate) {
        if (dayOfWeek === 0 || dayOfWeek === 6) {
          alert("Reine Wochenend-Reservationen (nur Samstag/Sonntag) sind nur auf Anfrage möglich. Bitte kontaktiere uns.");
          return;
        }
        this.selectedStartDate = dateStr;
      } else {
        let hasReservedInBetween = false;
        let curr = new Date(this.selectedStartDate);
        const target = new Date(dateStr);

        while (curr <= target) {
          const y = curr.getFullYear();
          const m = curr.getMonth();
          const d = curr.getDate();
          const checkStr = this.formatDateStr(y, m, d);
          if (this.isDateReserved(checkStr)) {
            hasReservedInBetween = true;
            break;
          }
          curr.setDate(curr.getDate() + 1);
        }

        if (hasReservedInBetween) {
          alert("Dieser Zeitraum enthält bereits belegte Tage. Eine Miete ist für diesen Zeitraum nur auf Anfrage möglich.");
          this.selectedStartDate = dateStr;
          this.selectedEndDate = null;
        } else {
          if (dayOfWeek === 6 || dayOfWeek === 0) {
            const mondayTarget = new Date(dateStr);
            const addDays = dayOfWeek === 6 ? 2 : 1;
            mondayTarget.setDate(mondayTarget.getDate() + addDays);
            const mondayStr = mondayTarget.toISOString().split("T")[0];
            
            if (!this.isDateReserved(mondayStr)) {
              this.selectedEndDate = mondayStr;
            } else {
              this.selectedEndDate = dateStr;
            }
          } else {
            this.selectedEndDate = dateStr;
          }
        }
      }
    }

    this.render();
    this.onSelectRange(this.selectedStartDate, this.selectedEndDate);
  }

  render() {
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    const monthNames = ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"];
    const weekdayShortNames = ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"];

    const todayStr = new Date().toISOString().split("T")[0];

    const firstDayIndex = new Date(year, month, 1).getDay();
    const mondayOffset = (firstDayIndex + 6) % 7;

    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let html = `
      <div class="calendar-widget">
        <div class="calendar-header">
          <button type="button" class="calendar-nav-btn" id="cal-prev">‹</button>
          <div class="calendar-month-title">${monthNames[month]} ${year}</div>
          <button type="button" class="calendar-nav-btn" id="cal-next">›</button>
        </div>

        <div class="calendar-legend">
          <span><span class="legend-dot free"></span> Frei</span>
          <span><span class="legend-dot reserved"></span> Belegt / Gesperrt</span>
          <span><span class="legend-dot selected"></span> Gewählter Zeitraum</span>
        </div>

        <div class="calendar-grid">
          <div class="cal-day-name">Mo</div>
          <div class="cal-day-name">Di</div>
          <div class="cal-day-name">Mi</div>
          <div class="cal-day-name">Do</div>
          <div class="cal-day-name" style="color: var(--orange-dark); font-weight:900;">Fr</div>
          <div class="cal-day-name" style="color: #64748b;">Sa</div>
          <div class="cal-day-name" style="color: #64748b;">So</div>
    `;

    for (let i = 0; i < mondayOffset; i++) {
      html += `<div class="cal-day empty"></div>`;
    }

    for (let day = 1; day <= daysInMonth; day++) {
      const dateStr = this.formatDateStr(year, month, day);
      const dateObj = new Date(year, month, day);
      const dayOfWeek = dateObj.getDay(); // 0 = So, 1 = Mo, 5 = Fr, 6 = Sa
      const weekdayLabel = weekdayShortNames[dayOfWeek];

      const isReserved = this.isDateReserved(dateStr);
      const isPast = dateStr < todayStr;
      const isToday = dateStr === todayStr;

      const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

      const isSelectedStart = dateStr === this.selectedStartDate;
      const isSelectedEnd = dateStr === this.selectedEndDate;
      const isInSelectedRange = this.selectedStartDate && this.selectedEndDate && dateStr >= this.selectedStartDate && dateStr <= this.selectedEndDate;

      let dayClasses = ["cal-day"];
      if (isPast) dayClasses.push("past");
      if (isReserved) dayClasses.push("reserved");
      if (!isReserved && !isPast) dayClasses.push("free");
      if (isToday) dayClasses.push("today");
      if (isSelectedStart) dayClasses.push("selected-start");
      if (isSelectedEnd) dayClasses.push("selected-end");
      if (isInSelectedRange) dayClasses.push("in-range");

      let badgeHtml = "";
      if (isReserved) {
        badgeHtml = `<span class="cal-status-badge reserved">Belegt</span>`;
      } else if (isPast) {
        badgeHtml = `<span class="cal-status-badge past">-</span>`;
      } else {
        badgeHtml = `<span class="cal-status-badge free" style="color:${isWeekend ? '#94a3b8' : '#16a34a'}; font-size:11px;">${isWeekend ? 'Wochenende' : 'Frei'}</span>`;
      }

      html += `
        <div class="${dayClasses.join(" ")}" data-date="${dateStr}">
          <span class="cal-day-num" style="font-size: 11px; opacity: 0.7;">${weekdayLabel}</span>
          <span class="cal-day-num" style="font-weight: 800; font-size: 15px;">${day}</span>
          ${badgeHtml}
        </div>
      `;
    }

    html += `</div></div>`;

    this.container.innerHTML = html;

    this.container.querySelector("#cal-prev").addEventListener("click", () => this.prevMonth());
    this.container.querySelector("#cal-next").addEventListener("click", () => this.nextMonth());

    this.container.querySelectorAll(".cal-day.free").forEach(dayEl => {
      dayEl.addEventListener("click", () => {
        const dateStr = dayEl.getAttribute("data-date");
        this.handleDayClick(dateStr, false, false);
      });
    });
  }
}
