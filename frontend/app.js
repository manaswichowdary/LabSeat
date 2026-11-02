(function () {
  const reportsBody = document.getElementById('reportsBody');
  const emptyState = document.getElementById('emptyState');
  const loadingOverlay = document.getElementById('loadingOverlay');
  const errorBanner = document.getElementById('errorBanner');
  const refreshBtn = document.getElementById('refreshBtn');

  const filterBuilding = document.getElementById('filterBuilding');
  const filterSeat = document.getElementById('filterSeatAvailability');
  const filterNoise = document.getElementById('filterNoise');
  const filterFlagged = document.getElementById('filterFlagged');

  const statTotal = document.getElementById('statTotal');
  const statHighAvail = document.getElementById('statHighAvail');
  const statQuiet = document.getElementById('statQuiet');
  const statFlagged = document.getElementById('statFlagged');

  const createReportForm = document.getElementById('createReportForm');
  const updateReportForm = document.getElementById('updateReportForm');
  const deleteReportForm = document.getElementById('deleteReportForm');

  const createMessage = document.getElementById('createMessage');
  const updateMessage = document.getElementById('updateMessage');
  const deleteMessage = document.getElementById('deleteMessage');

  let allReports = [];

  function setLoading(on) {
    if (on) {
      loadingOverlay.removeAttribute('hidden');
      loadingOverlay.removeAttribute('aria-hidden');
    } else {
      loadingOverlay.setAttribute('hidden', '');
      loadingOverlay.setAttribute('aria-hidden', 'true');
    }

    refreshBtn.disabled = on;
  }

  function showError(message) {
    errorBanner.textContent = message;
    errorBanner.hidden = false;
  }

  function clearError() {
    errorBanner.textContent = '';
    errorBanner.hidden = true;
  }

  function setFormMessage(element, message, type) {
    element.textContent = message;
    element.classList.remove('success', 'error');

    if (type) {
      element.classList.add(type);
    }
  }

  function clearFormMessages() {
    setFormMessage(createMessage, '', '');
    setFormMessage(updateMessage, '', '');
    setFormMessage(deleteMessage, '', '');
  }

  function uniqueSorted(values) {
    return [...new Set(values.filter((v) => v != null && String(v).trim() !== ''))].sort(
      (a, b) => String(a).localeCompare(String(b), undefined, { sensitivity: 'base' })
    );
  }

  function truthyFlag(val) {
    if (typeof val === 'boolean') {
      return val;
    }

    const s = String(val).toLowerCase();
    return s === '1' || s === 'true' || s === 't' || s === 'yes';
  }

  function normalizeReportsResponse(data) {
    if (Array.isArray(data)) {
      return data;
    }

    if (data && Array.isArray(data.reports)) {
      return data.reports;
    }

    throw new Error('Expected get_reports.php to return a JSON array or an object with a reports array.');
  }

  function populateFilterOptions() {
    const buildings = uniqueSorted(allReports.map((r) => r.BuildingName));
    const seats = uniqueSorted(allReports.map((r) => r.SeatAvailability));
    const noises = uniqueSorted(allReports.map((r) => r.NoiseLevel));

    const buildingVal = filterBuilding.value;
    filterBuilding.innerHTML = '<option value="">All buildings</option>';
    buildings.forEach((b) => {
      const opt = document.createElement('option');
      opt.value = b;
      opt.textContent = b;
      filterBuilding.appendChild(opt);
    });
    if (buildings.includes(buildingVal)) {
      filterBuilding.value = buildingVal;
    }

    const seatVal = filterSeat.value;
    filterSeat.innerHTML = '<option value="">Any</option>';
    seats.forEach((s) => {
      const opt = document.createElement('option');
      opt.value = s;
      opt.textContent = s;
      filterSeat.appendChild(opt);
    });
    if (seats.includes(seatVal)) {
      filterSeat.value = seatVal;
    }

    const noiseVal = filterNoise.value;
    filterNoise.innerHTML = '<option value="">Any</option>';
    noises.forEach((n) => {
      const opt = document.createElement('option');
      opt.value = n;
      opt.textContent = n;
      filterNoise.appendChild(opt);
    });
    if (noises.includes(noiseVal)) {
      filterNoise.value = noiseVal;
    }

    const flaggedVal = filterFlagged.value;
    filterFlagged.innerHTML =
      '<option value="">Any</option><option value="yes">Yes</option><option value="no">No</option>';
    filterFlagged.value = flaggedVal || '';
  }

  function computeSummaryStats(rows) {
    let highAvail = 0;
    let quiet = 0;
    let flagged = 0;

    rows.forEach((r) => {
      const seat = String(r.SeatAvailability ?? '').toLowerCase();
      if (seat === 'high') {
        highAvail++;
      }

      const noise = String(r.NoiseLevel ?? '').toLowerCase();
      if (noise === 'quiet') {
        quiet++;
      }

      if (truthyFlag(r.IsFlagged)) {
        flagged++;
      }
    });

    return {
      total: rows.length,
      highAvail,
      quiet,
      flagged,
    };
  }

  function passesFilters(r) {
    if (filterBuilding.value && r.BuildingName !== filterBuilding.value) {
      return false;
    }

    if (filterSeat.value && String(r.SeatAvailability) !== filterSeat.value) {
      return false;
    }

    if (filterNoise.value && String(r.NoiseLevel) !== filterNoise.value) {
      return false;
    }

    if (filterFlagged.value) {
      const isF = truthyFlag(r.IsFlagged);

      if (filterFlagged.value === 'yes' && !isF) {
        return false;
      }

      if (filterFlagged.value === 'no' && isF) {
        return false;
      }
    }

    return true;
  }

  function renderFlagCell(isFlagged) {
    return truthyFlag(isFlagged)
      ? '<span class="badge flagged">Yes</span>'
      : '<span class="badge">No</span>';
  }

  function escapeHtml(text) {
    if (text == null) {
      return '';
    }

    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function renderTable() {
    const filtered = allReports.filter(passesFilters);
    const frag = document.createDocumentFragment();

    filtered.forEach((r) => {
      const tr = document.createElement('tr');

      tr.innerHTML = [
        escapeHtml(r.ReportID),
        escapeHtml(r.BuildingName),
        escapeHtml(r.FloorNumber),
        escapeHtml(r.ZoneName),
        escapeHtml(r.UserName),
        escapeHtml(r.SeatAvailability),
        escapeHtml(r.NoiseLevel),
        escapeHtml(r.OutletAvailability),
        escapeHtml(r.CreatedAt),
        escapeHtml(r.ExpiresAt),
        renderFlagCell(r.IsFlagged),
      ]
        .map((cell) => `<td>${cell}</td>`)
        .join('');

      frag.appendChild(tr);
    });

    reportsBody.replaceChildren(frag);
    emptyState.hidden = filtered.length !== 0;

    const summary = computeSummaryStats(filtered);
    statTotal.textContent = String(summary.total);
    statHighAvail.textContent = String(summary.highAvail);
    statQuiet.textContent = String(summary.quiet);
    statFlagged.textContent = String(summary.flagged);
  }

  async function loadReports() {
    clearError();
    setLoading(true);

    try {
      const res = await fetch('../backend/get_reports.php', { cache: 'no-store' });

      if (!res.ok) {
        throw new Error('Request failed with status ' + res.status);
      }

      const data = await res.json();
      allReports = normalizeReportsResponse(data);

      populateFilterOptions();
      renderTable();
    } catch (e) {
      allReports = [];
      reportsBody.replaceChildren();
      emptyState.hidden = true;

      showError(
        'Could not load reports. ' +
          (e && e.message ? e.message : 'Check that get_reports.php is reachable.') +
          ' Use Refresh to try again.'
      );

      statTotal.textContent = '0';
      statHighAvail.textContent = '0';
      statQuiet.textContent = '0';
      statFlagged.textContent = '0';
    } finally {
      setLoading(false);
    }
  }

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    let data = null;

    try {
      data = await res.json();
    } catch (e) {
      throw new Error('Server did not return valid JSON.');
    }

    if (!res.ok || data.ok === false) {
      throw new Error(data.error || data.message || 'Request failed.');
    }

    return data;
  }

  function getCreatePayload() {
    return {
      ZoneID: Number(document.getElementById('createZoneID').value),
      UserID: Number(document.getElementById('createUserID').value),
      SeatAvailability: document.getElementById('createSeatAvailability').value,
      NoiseLevel: document.getElementById('createNoiseLevel').value,
      OutletAvailability: document.getElementById('createOutletAvailability').value,
    };
  }

  function getUpdatePayload() {
    return {
      ReportID: Number(document.getElementById('updateReportID').value),
      SeatAvailability: document.getElementById('updateSeatAvailability').value,
      NoiseLevel: document.getElementById('updateNoiseLevel').value,
      OutletAvailability: document.getElementById('updateOutletAvailability').value,
      IsFlagged: document.getElementById('updateIsFlagged').value === 'true',
    };
  }

  function getDeletePayload() {
    return {
      ReportID: Number(document.getElementById('deleteReportID').value),
    };
  }

  async function handleCreateReport(event) {
    event.preventDefault();
    clearError();
    setFormMessage(createMessage, 'Adding report…', '');

    const submitBtn = createReportForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const payload = getCreatePayload();
      const result = await postJson('../backend/add_report.php', payload);

      setFormMessage(
        createMessage,
        result.message || 'Report added successfully. Refreshing table…',
        'success'
      );

      createReportForm.reset();
      await loadReports();
    } catch (e) {
      setFormMessage(createMessage, e.message || 'Could not add report.', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  }

  async function handleUpdateReport(event) {
    event.preventDefault();
    clearError();
    setFormMessage(updateMessage, 'Updating report…', '');

    const submitBtn = updateReportForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const payload = getUpdatePayload();
      const result = await postJson('../backend/update_report.php', payload);

      setFormMessage(
        updateMessage,
        result.message || 'Report updated successfully. Refreshing table…',
        'success'
      );

      updateReportForm.reset();
      document.getElementById('updateIsFlagged').value = 'false';
      await loadReports();
    } catch (e) {
      setFormMessage(updateMessage, e.message || 'Could not update report.', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  }

  async function handleDeleteReport(event) {
    event.preventDefault();
    clearError();

    const payload = getDeletePayload();

    const confirmed = window.confirm(
      'Are you sure you want to delete ReportID ' + payload.ReportID + '?'
    );

    if (!confirmed) {
      setFormMessage(deleteMessage, 'Delete cancelled.', '');
      return;
    }

    setFormMessage(deleteMessage, 'Deleting report…', '');

    const submitBtn = deleteReportForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const result = await postJson('../backend/delete_report.php', payload);

      setFormMessage(
        deleteMessage,
        result.message || 'Report deleted successfully. Refreshing table…',
        'success'
      );

      deleteReportForm.reset();
      await loadReports();
    } catch (e) {
      setFormMessage(deleteMessage, e.message || 'Could not delete report.', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  }

  refreshBtn.addEventListener('click', loadReports);

  [filterBuilding, filterSeat, filterNoise, filterFlagged].forEach((el) => {
    el.addEventListener('change', renderTable);
  });

  createReportForm.addEventListener('submit', handleCreateReport);
  updateReportForm.addEventListener('submit', handleUpdateReport);
  deleteReportForm.addEventListener('submit', handleDeleteReport);

  window.addEventListener('DOMContentLoaded', function () {
    clearFormMessages();
    loadReports();
  });
})();
