import "./style.css";

const STORAGE_KEY = "assignment-sample-frontend-state";
const ASSET_CODES = ["Cash", "Conservative", "Balanced", "Growth", "HighGrowth"];
const DEFAULT_PRICE = 1.0;

const state = loadState();
const ui = {};

document.querySelector("#app").innerHTML = `
  <div class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <p class="eyebrow">Sample UX</p>
        <h1>Super System Console</h1>
        <p class="caption">
          Minimal interface for exercising the assignment API.
        </p>
      </div>

      <div class="sidebar-section">
        <label class="field-label" for="api-base-input">API Base</label>
        <input id="api-base-input" type="text" />
      </div>

      <nav class="nav-list">
        <button class="nav-button active" data-view="create-view">
          Create Member
        </button>
        <button class="nav-button" data-view="member-view">
          Member Workspace
        </button>
        <button class="nav-button" data-view="admin-view">
          Admin Workspace
        </button>
      </nav>

      <div class="sidebar-section">
        <div class="section-header">
          <h2>Known Users</h2>
          <button id="clear-local-state-button" class="ghost-button" type="button">
            Clear Cache
          </button>
        </div>
        <div id="known-users-list" class="known-users-list"></div>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <p class="eyebrow">Runtime</p>
          <h2>localhost:9001</h2>
        </div>
        <div id="flash-message" class="flash-message" aria-live="polite"></div>
      </header>

      <section id="create-view" class="view active">
        <div class="view-header">
          <div>
            <p class="eyebrow">Public API</p>
            <h2>Create Member</h2>
          </div>
        </div>

        <div class="card-grid">
          <section class="card">
            <h3>Create Member Request</h3>
            <form id="create-member-form" class="form-grid">
              <label>
                <span class="field-label">User ID</span>
                <input id="create-user-id" type="text" required />
              </label>
              <label>
                <span class="field-label">First Name</span>
                <input id="create-first-name" type="text" required />
              </label>
              <label>
                <span class="field-label">Last Name</span>
                <input id="create-last-name" type="text" required />
              </label>
              <label>
                <span class="field-label">Email</span>
                <input id="create-email" type="email" required />
              </label>
              <label>
                <span class="field-label">Mobile</span>
                <input id="create-mobile" type="text" required />
              </label>
              <label>
                <span class="field-label">Date of Birth</span>
                <input id="create-date-of-birth" type="date" required />
              </label>

              <div class="allocation-panel">
                <div class="section-header">
                  <h4>Initial Investment Profile</h4>
                  <span id="create-allocation-total" class="pill">Total: 100%</span>
                </div>
                <div id="create-allocation-fields" class="allocation-fields"></div>
              </div>

              <div class="form-actions">
                <button type="submit" class="primary-button">Create Member</button>
              </div>
            </form>
          </section>

          <section class="card">
            <div class="section-header">
              <h3>Response</h3>
            </div>
            <pre id="create-member-response" class="response-panel"></pre>
          </section>
        </div>
      </section>

      <section id="member-view" class="view">
        <div class="view-header">
          <div>
            <p class="eyebrow">Member Workspace</p>
            <h2>Member Data</h2>
          </div>
          <div class="header-actions">
            <button id="refresh-member-data-button" class="primary-button" type="button">
              Refresh All
            </button>
          </div>
        </div>

        <div id="member-empty-state" class="empty-state">
          Select or create a user to view member data.
        </div>

        <div id="member-content" class="stack hidden">
          <section class="card member-summary-card">
            <div class="summary-grid">
              <div>
                <p class="summary-label">User ID</p>
                <p id="member-summary-user-id" class="summary-value"></p>
              </div>
              <div>
                <p class="summary-label">Member ID</p>
                <p id="member-summary-member-id" class="summary-value"></p>
              </div>
              <div>
                <p class="summary-label">Account ID</p>
                <p id="member-summary-account-id" class="summary-value"></p>
              </div>
            </div>
          </section>

          <div class="card-grid">
            <section class="card">
              <h3>Update Member</h3>
              <form id="update-member-form" class="form-grid compact">
                <label>
                  <span class="field-label">Email</span>
                  <input id="update-email" type="email" />
                </label>
                <label>
                  <span class="field-label">Mobile</span>
                  <input id="update-mobile" type="text" />
                </label>
                <label>
                  <span class="field-label">Preferred Name</span>
                  <input id="update-preferred-name" type="text" />
                </label>
                <div class="form-actions">
                  <button type="submit" class="secondary-button">Update Member</button>
                </div>
              </form>
              <pre id="update-member-response" class="response-panel small"></pre>
            </section>

            <section class="card">
              <div class="section-header">
                <h3>Set Investment Profile</h3>
                <span id="member-allocation-total" class="pill">Total: 0%</span>
              </div>
              <form id="set-profile-form" class="form-grid compact">
                <div id="member-profile-fields" class="allocation-fields"></div>
                <div class="form-actions">
                  <button type="submit" class="secondary-button">Set Profile</button>
                </div>
              </form>
              <pre id="set-profile-response" class="response-panel small"></pre>
            </section>
          </div>

          <div class="card-grid">
            <section class="card">
              <div class="section-header">
                <h3>Investment Portfolio</h3>
                <button id="load-portfolio-button" class="ghost-button" type="button">
                  Load
                </button>
              </div>
              <div id="portfolio-table-container"></div>
              <pre id="portfolio-response" class="response-panel small"></pre>
            </section>

            <section class="card">
              <div class="section-header">
                <h3>Transactions</h3>
                <button id="load-transactions-button" class="ghost-button" type="button">
                  Load
                </button>
              </div>
              <div id="transactions-table-container"></div>
              <pre id="transactions-response" class="response-panel small"></pre>
            </section>
          </div>

          <section class="card">
            <div class="section-header">
              <h3>Holdings</h3>
              <button id="load-holdings-button" class="ghost-button" type="button">
                Load
              </button>
            </div>
            <div id="holdings-table-container"></div>
            <pre id="holdings-response" class="response-panel small"></pre>
          </section>
        </div>
      </section>

      <section id="admin-view" class="view">
        <div class="view-header">
          <div>
            <p class="eyebrow">Admin Workspace</p>
            <h2>Mock Controls & Audit</h2>
          </div>
        </div>

        <div class="card-grid">
          <section class="card">
            <h3>Mock Admin Controls</h3>
            <div id="admin-empty-state" class="empty-state small">
              Select a known user to use mock admin controls.
            </div>

            <div id="admin-controls" class="stack hidden">
              <form id="add-transaction-form" class="form-grid compact">
                <h4>Add Transaction</h4>
                <label>
                  <span class="field-label">Effective Date</span>
                  <input id="transaction-date" type="date" required />
                </label>
                <label>
                  <span class="field-label">Type</span>
                  <input id="transaction-type" type="text" value="Contribution" required />
                </label>
                <label>
                  <span class="field-label">Amount</span>
                  <input id="transaction-amount" type="number" step="0.01" required />
                </label>
                <div class="form-actions">
                  <button type="submit" class="secondary-button">Add Transaction</button>
                </div>
              </form>

              <form id="set-prices-form" class="form-grid compact">
                <div class="section-header">
                  <h4>Set Daily Unit Prices</h4>
                </div>
                <label>
                  <span class="field-label">Date</span>
                  <input id="unit-price-date" type="date" required />
                </label>
                <div id="unit-price-fields" class="allocation-fields"></div>
                <div class="form-actions">
                  <button type="submit" class="secondary-button">Set Prices</button>
                </div>
              </form>

              <form id="move-day-form" class="form-grid compact">
                <h4>Move Day Forward</h4>
                <label>
                  <span class="field-label">Days</span>
                  <input id="move-day-count" type="number" min="1" value="1" />
                </label>
                <div class="form-actions">
                  <button type="submit" class="secondary-button">Advance Day</button>
                </div>
              </form>

              <div class="form-actions">
                <button id="reset-user-button" class="danger-button" type="button">
                  Reset Selected User
                </button>
              </div>
            </div>

            <pre id="mock-response" class="response-panel"></pre>
          </section>

          <section class="card">
            <div class="section-header">
              <h3>Audit Events</h3>
              <button id="load-audit-events-button" class="ghost-button" type="button">
                Load Selected User Audit
              </button>
            </div>
            <div id="audit-users-list" class="audit-user-list"></div>
            <div id="audit-events-table-container"></div>
            <pre id="audit-events-response" class="response-panel small"></pre>
          </section>
        </div>

        <section class="card">
          <div class="section-header">
            <h3>Request Audit</h3>
          </div>
          <div id="operation-buttons" class="operation-buttons"></div>
          <form id="request-audit-form" class="form-grid compact">
            <label>
              <span class="field-label">Operation ID</span>
              <input id="request-audit-operation-id" type="text" required />
            </label>
            <div class="form-actions">
              <button type="submit" class="secondary-button">Load Request Audit</button>
            </div>
          </form>
          <pre id="request-audit-response" class="response-panel"></pre>
        </section>
      </section>
    </main>
  </div>
`;

init();

function init() {
  cacheElements();
  renderAllocationInputs("create-allocation-fields", "create");
  renderAllocationInputs("member-profile-fields", "member");
  renderUnitPriceInputs();

  ui.apiBaseInput.value = state.apiBase;

  bindNavigation();
  bindForms();
  bindButtons();
  syncCreateDefaults();
  renderAll();
}

function cacheElements() {
  ui.apiBaseInput = document.getElementById("api-base-input");
  ui.flashMessage = document.getElementById("flash-message");
  ui.knownUsersList = document.getElementById("known-users-list");
  ui.auditUsersList = document.getElementById("audit-users-list");
  ui.operationButtons = document.getElementById("operation-buttons");

  ui.createMemberForm = document.getElementById("create-member-form");
  ui.createUserId = document.getElementById("create-user-id");
  ui.createFirstName = document.getElementById("create-first-name");
  ui.createLastName = document.getElementById("create-last-name");
  ui.createEmail = document.getElementById("create-email");
  ui.createMobile = document.getElementById("create-mobile");
  ui.createDateOfBirth = document.getElementById("create-date-of-birth");
  ui.createResponse = document.getElementById("create-member-response");
  ui.createTotal = document.getElementById("create-allocation-total");

  ui.memberEmptyState = document.getElementById("member-empty-state");
  ui.memberContent = document.getElementById("member-content");
  ui.memberSummaryUserId = document.getElementById("member-summary-user-id");
  ui.memberSummaryMemberId = document.getElementById("member-summary-member-id");
  ui.memberSummaryAccountId = document.getElementById("member-summary-account-id");
  ui.refreshMemberDataButton = document.getElementById("refresh-member-data-button");
  ui.updateMemberForm = document.getElementById("update-member-form");
  ui.updateEmail = document.getElementById("update-email");
  ui.updateMobile = document.getElementById("update-mobile");
  ui.updatePreferredName = document.getElementById("update-preferred-name");
  ui.updateResponse = document.getElementById("update-member-response");
  ui.memberTotal = document.getElementById("member-allocation-total");
  ui.setProfileForm = document.getElementById("set-profile-form");
  ui.setProfileResponse = document.getElementById("set-profile-response");
  ui.loadPortfolioButton = document.getElementById("load-portfolio-button");
  ui.loadTransactionsButton = document.getElementById("load-transactions-button");
  ui.loadHoldingsButton = document.getElementById("load-holdings-button");
  ui.portfolioContainer = document.getElementById("portfolio-table-container");
  ui.transactionsContainer = document.getElementById("transactions-table-container");
  ui.holdingsContainer = document.getElementById("holdings-table-container");
  ui.portfolioResponse = document.getElementById("portfolio-response");
  ui.transactionsResponse = document.getElementById("transactions-response");
  ui.holdingsResponse = document.getElementById("holdings-response");

  ui.adminEmptyState = document.getElementById("admin-empty-state");
  ui.adminControls = document.getElementById("admin-controls");
  ui.addTransactionForm = document.getElementById("add-transaction-form");
  ui.transactionDate = document.getElementById("transaction-date");
  ui.transactionType = document.getElementById("transaction-type");
  ui.transactionAmount = document.getElementById("transaction-amount");
  ui.setPricesForm = document.getElementById("set-prices-form");
  ui.unitPriceDate = document.getElementById("unit-price-date");
  ui.moveDayForm = document.getElementById("move-day-form");
  ui.moveDayCount = document.getElementById("move-day-count");
  ui.resetUserButton = document.getElementById("reset-user-button");
  ui.mockResponse = document.getElementById("mock-response");
  ui.loadAuditEventsButton = document.getElementById("load-audit-events-button");
  ui.auditEventsContainer = document.getElementById("audit-events-table-container");
  ui.auditEventsResponse = document.getElementById("audit-events-response");
  ui.requestAuditForm = document.getElementById("request-audit-form");
  ui.requestAuditOperationId = document.getElementById("request-audit-operation-id");
  ui.requestAuditResponse = document.getElementById("request-audit-response");
  ui.clearLocalStateButton = document.getElementById("clear-local-state-button");
}

function bindNavigation() {
  document.querySelectorAll(".nav-button").forEach((button) => {
    button.addEventListener("click", () => switchView(button.dataset.view));
  });
}

function bindForms() {
  ui.apiBaseInput.addEventListener("change", () => {
    state.apiBase = ui.apiBaseInput.value.trim() || defaultApiBase();
    saveState();
    flash(`API base set to ${state.apiBase}`, "success");
  });

  ui.createMemberForm.addEventListener("submit", onCreateMember);
  ui.updateMemberForm.addEventListener("submit", onUpdateMember);
  ui.setProfileForm.addEventListener("submit", onSetProfile);
  ui.addTransactionForm.addEventListener("submit", onAddTransaction);
  ui.setPricesForm.addEventListener("submit", onSetUnitPrices);
  ui.moveDayForm.addEventListener("submit", onMoveDayForward);
  ui.requestAuditForm.addEventListener("submit", onGetRequestAudit);

  for (const prefix of ["create", "member"]) {
    ASSET_CODES.forEach((code) => {
      document
        .getElementById(`${prefix}-allocation-${code}`)
        .addEventListener("input", () => updateAllocationTotal(prefix));
    });
  }
}

function bindButtons() {
  ui.refreshMemberDataButton.addEventListener("click", refreshAllMemberData);
  ui.loadPortfolioButton.addEventListener("click", loadPortfolio);
  ui.loadTransactionsButton.addEventListener("click", loadTransactions);
  ui.loadHoldingsButton.addEventListener("click", loadHoldings);
  ui.loadAuditEventsButton.addEventListener("click", loadAuditEventsForSelectedUser);
  ui.resetUserButton.addEventListener("click", onResetSelectedUser);
  ui.clearLocalStateButton.addEventListener("click", clearLocalCache);
}

function syncCreateDefaults() {
  const today = new Date().toISOString().slice(0, 10);
  if (!ui.createUserId.value) ui.createUserId.value = "demo-user-1";
  if (!ui.createFirstName.value) ui.createFirstName.value = "Jordan";
  if (!ui.createLastName.value) ui.createLastName.value = "Lee";
  if (!ui.createEmail.value) ui.createEmail.value = "jordan.lee@example.com";
  if (!ui.createMobile.value) ui.createMobile.value = "0400000000";
  ui.createDateOfBirth.value = "1990-01-01";
  ui.transactionDate.value = today;
  ui.unitPriceDate.value = today;
  writeAllocations("create", [{ assetCode: "Balanced", percentage: 100 }]);
  writeAllocations("member", [{ assetCode: "Balanced", percentage: 100 }]);
  updateAllocationTotal("create");
  updateAllocationTotal("member");
  renderEmpty(ui.portfolioContainer, "No portfolio data loaded.");
  renderEmpty(ui.transactionsContainer, "No transaction data loaded.");
  renderEmpty(ui.holdingsContainer, "No holdings data loaded.");
  renderEmpty(ui.auditEventsContainer, "No audit events loaded.");
}

function switchView(viewId) {
  document.querySelectorAll(".view").forEach((view) => {
    view.classList.toggle("active", view.id === viewId);
  });
  document.querySelectorAll(".nav-button").forEach((button) => {
    button.classList.toggle("active", button.dataset.view === viewId);
  });
}

function renderAll() {
  renderKnownUsers();
  renderSelectedUser();
  renderAdminUsers();
  renderOperationButtons();
  updateAllocationTotal("create");
  updateAllocationTotal("member");
}

function renderKnownUsers() {
  ui.knownUsersList.innerHTML = "";

  if (!state.users.length) {
    ui.knownUsersList.innerHTML = '<div class="empty-state small">No users created yet.</div>';
    return;
  }

  for (const user of state.users) {
    const button = document.createElement("button");
    button.type = "button";
    button.className = `known-user-button ${state.selectedUserId === user.userId ? "active" : ""}`;
    button.innerHTML = `
      <strong>${escapeHtml(user.name || user.userId)}</strong>
      <small>User ID: ${escapeHtml(user.userId)}</small>
      <small>Member: ${escapeHtml(user.memberId || "pending")}</small>
    `;
    button.addEventListener("click", () => {
      state.selectedUserId = user.userId;
      saveState();
      renderAll();
      switchView("member-view");
    });
    ui.knownUsersList.appendChild(button);
  }
}

function renderSelectedUser() {
  const user = getSelectedUser();

  if (!user) {
    ui.memberEmptyState.classList.remove("hidden");
    ui.memberContent.classList.add("hidden");
    ui.adminEmptyState.classList.remove("hidden");
    ui.adminControls.classList.add("hidden");
    return;
  }

  ui.memberEmptyState.classList.add("hidden");
  ui.memberContent.classList.remove("hidden");
  ui.adminEmptyState.classList.add("hidden");
  ui.adminControls.classList.remove("hidden");

  ui.memberSummaryUserId.textContent = user.userId;
  ui.memberSummaryMemberId.textContent = user.memberId || "Not created yet";
  ui.memberSummaryAccountId.textContent = user.accountId || "Not created yet";

  ui.updateEmail.value = user.email || "";
  ui.updateMobile.value = user.mobile || "";
  ui.updatePreferredName.value = user.preferredName || "";

  if (user.allocations) {
    writeAllocations("member", user.allocations);
  } else {
    writeAllocations("member", [{ assetCode: "Balanced", percentage: 100 }]);
  }

  updateAllocationTotal("member");
}

function renderAdminUsers() {
  ui.auditUsersList.innerHTML = "";

  if (!state.users.length) {
    ui.auditUsersList.innerHTML = '<div class="empty-state small">No known users to inspect.</div>';
    return;
  }

  for (const user of state.users) {
    const card = document.createElement("div");
    card.className = "audit-user-card";
    card.innerHTML = `
      <strong>${escapeHtml(user.name || user.userId)}</strong>
      <small>User ID: ${escapeHtml(user.userId)}</small>
      <small>Member ID: ${escapeHtml(user.memberId || "pending")}</small>
      <small>Account ID: ${escapeHtml(user.accountId || "pending")}</small>
    `;

    const button = document.createElement("button");
    button.type = "button";
    button.className = "ghost-button";
    button.textContent = "Load Audit Events";
    button.addEventListener("click", async () => {
      state.selectedUserId = user.userId;
      saveState();
      renderAll();
      switchView("admin-view");
      await loadAuditEventsForSelectedUser();
    });

    card.appendChild(button);
    ui.auditUsersList.appendChild(card);
  }
}

function renderOperationButtons() {
  ui.operationButtons.innerHTML = "";
  const user = getSelectedUser();

  if (!user || !user.operations || !user.operations.length) {
    ui.operationButtons.innerHTML = '<div class="empty-state small">No operation IDs recorded yet.</div>';
    return;
  }

  for (const operation of user.operations) {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "operation-button";
    button.innerHTML = `
      <strong>${escapeHtml(operation.label)}</strong>
      <small>${escapeHtml(operation.operationId)}</small>
    `;
    button.addEventListener("click", async () => {
      ui.requestAuditOperationId.value = operation.operationId;
      await loadRequestAudit(operation.operationId);
    });
    ui.operationButtons.appendChild(button);
  }
}

async function onCreateMember(event) {
  event.preventDefault();

  const payload = {
    userId: ui.createUserId.value.trim(),
    firstName: ui.createFirstName.value.trim(),
    lastName: ui.createLastName.value.trim(),
    email: ui.createEmail.value.trim(),
    mobile: ui.createMobile.value.trim(),
    dateOfBirth: ui.createDateOfBirth.value,
    initialInvestmentProfile: readAllocations("create"),
  };

  const result = await postJson("/public/createMember", payload);
  writeResponse(ui.createResponse, result);

  if (isSuccess(result)) {
    const body = result.body;
    upsertUser({
      userId: payload.userId,
      memberId: body.memberId,
      accountId: body.accountId,
      name: `${payload.firstName} ${payload.lastName}`.trim(),
      email: payload.email,
      mobile: payload.mobile,
      allocations: payload.initialInvestmentProfile,
    });
    recordOperation(payload.userId, body.operationId, "Create Member");
    state.selectedUserId = payload.userId;
    saveState();
    renderAll();
    flash("Member created successfully.", "success");
    switchView("member-view");
    await refreshAllMemberData();
  } else {
    flash("Create member failed. Check the response panel.", "error");
  }
}

async function onUpdateMember(event) {
  event.preventDefault();
  const user = requireSelectedUser();
  if (!user || !user.memberId) {
    return;
  }

  const payload = {
    userId: user.userId,
    memberId: user.memberId,
    email: ui.updateEmail.value.trim() || undefined,
    mobile: ui.updateMobile.value.trim() || undefined,
    preferredName: ui.updatePreferredName.value.trim() || undefined,
  };

  const result = await postJson("/public/updateMember", payload);
  writeResponse(ui.updateResponse, result);

  if (isSuccess(result)) {
    user.email = payload.email || user.email;
    user.mobile = payload.mobile || user.mobile;
    user.preferredName = payload.preferredName || user.preferredName;
    recordOperation(user.userId, result.body.operationId, "Update Member");
    saveState();
    renderAll();
    flash("Member updated.", "success");
  } else {
    flash("Update member failed.", "error");
  }
}

async function onSetProfile(event) {
  event.preventDefault();
  const user = requireSelectedUser();
  if (!user || !user.memberId || !user.accountId) {
    return;
  }

  const allocations = readAllocations("member");
  const payload = {
    userId: user.userId,
    memberId: user.memberId,
    accountId: user.accountId,
    allocations,
  };

  const result = await postJson("/public/setInvestmentProfile", payload);
  writeResponse(ui.setProfileResponse, result);

  if (isSuccess(result)) {
    user.allocations = allocations;
    recordOperation(user.userId, result.body.operationId, "Set Investment Profile");
    saveState();
    renderAll();
    flash("Investment profile updated.", "success");
    await loadPortfolio();
  } else {
    flash("Set investment profile failed.", "error");
  }
}

async function refreshAllMemberData() {
  await Promise.all([loadPortfolio(), loadTransactions(), loadHoldings()]);
}

async function loadPortfolio() {
  const user = requireSelectedUser();
  if (!user || !user.memberId || !user.accountId) {
    return;
  }

  const payload = {
    userId: user.userId,
    memberId: user.memberId,
    accountId: user.accountId,
  };

  const result = await postJson("/public/getInvestmentPortfolio", payload);
  writeResponse(ui.portfolioResponse, result);

  if (isSuccess(result) && Array.isArray(result.body.allocations)) {
    user.allocations = result.body.allocations;
    saveState();
    writeAllocations("member", result.body.allocations);
    renderTable(ui.portfolioContainer, result.body.allocations, [
      ["assetCode", "Asset"],
      ["percentage", "Percentage"],
    ]);
  } else {
    renderEmpty(ui.portfolioContainer, "No portfolio data loaded.");
  }
}

async function loadTransactions() {
  const user = requireSelectedUser();
  if (!user || !user.memberId || !user.accountId) {
    return;
  }

  const payload = {
    userId: user.userId,
    memberId: user.memberId,
    accountId: user.accountId,
  };

  const result = await postJson("/public/getTransactionHistory", payload);
  writeResponse(ui.transactionsResponse, result);

  if (isSuccess(result) && Array.isArray(result.body.transactions)) {
    renderTable(ui.transactionsContainer, result.body.transactions, [
      ["transactionId", "Transaction ID"],
      ["effectiveDate", "Date"],
      ["type", "Type"],
      ["amount", "Amount"],
    ]);
  } else {
    renderEmpty(ui.transactionsContainer, "No transaction data loaded.");
  }
}

async function loadHoldings() {
  const user = requireSelectedUser();
  if (!user || !user.memberId || !user.accountId) {
    return;
  }

  const payload = {
    userId: user.userId,
    memberId: user.memberId,
    accountId: user.accountId,
  };

  const result = await postJson("/public/getHoldings", payload);
  writeResponse(ui.holdingsResponse, result);

  if (isSuccess(result) && Array.isArray(result.body.holdings)) {
    renderTable(ui.holdingsContainer, result.body.holdings, [
      ["assetCode", "Asset"],
      ["units", "Units"],
      ["unitPrice", "Unit Price"],
      ["balance", "Balance"],
      ["effectiveDate", "Effective Date"],
    ]);
  } else {
    renderEmpty(ui.holdingsContainer, "No holdings data loaded.");
  }
}

async function onAddTransaction(event) {
  event.preventDefault();
  const user = requireSelectedUser();
  if (!user || !user.accountId) {
    return;
  }

  const payload = {
    userId: user.userId,
    accountId: user.accountId,
    transactions: [
      {
        effectiveDate: ui.transactionDate.value,
        type: ui.transactionType.value.trim(),
        amount: Number(ui.transactionAmount.value),
      },
    ],
  };

  const result = await postJson("/mock/addTransactions", payload);
  writeResponse(ui.mockResponse, result);

  if (isSuccess(result)) {
    flash("Transaction added.", "success");
    await loadTransactions();
  } else {
    flash("Add transaction failed.", "error");
  }
}

async function onSetUnitPrices(event) {
  event.preventDefault();

  const payload = {
    date: ui.unitPriceDate.value,
    prices: ASSET_CODES.map((assetCode) => ({
      assetCode,
      date: ui.unitPriceDate.value,
      unitPrice: Number(document.getElementById(`unit-price-${assetCode}`).value || DEFAULT_PRICE),
    })),
  };

  const result = await postJson("/mock/setDailyUnitPrices", payload);
  writeResponse(ui.mockResponse, result);

  if (isSuccess(result)) {
    flash("Unit prices set.", "success");
  } else {
    flash("Set unit prices failed.", "error");
  }
}

async function onMoveDayForward(event) {
  event.preventDefault();

  const payload = {
    days: Number(ui.moveDayCount.value || 1),
  };

  const result = await postJson("/mock/moveDayForward", payload);
  writeResponse(ui.mockResponse, result);

  if (isSuccess(result)) {
    flash("System day advanced.", "success");
    await Promise.all([loadTransactions(), loadHoldings()]);
  } else {
    flash("Move day forward failed.", "error");
  }
}

async function onResetSelectedUser() {
  const user = requireSelectedUser();
  if (!user) {
    return;
  }

  const confirmed = window.confirm(`Reset state for user "${user.userId}"?`);
  if (!confirmed) {
    return;
  }

  const result = await postJson("/mock/resetSubjectState", { userId: user.userId });
  writeResponse(ui.mockResponse, result);

  if (isSuccess(result)) {
    state.users = state.users.filter((entry) => entry.userId !== user.userId);
    if (state.selectedUserId === user.userId) {
      state.selectedUserId = state.users[0]?.userId || "";
    }
    saveState();
    renderAll();
    renderEmpty(ui.transactionsContainer, "No transaction data loaded.");
    renderEmpty(ui.holdingsContainer, "No holdings data loaded.");
    renderEmpty(ui.portfolioContainer, "No portfolio data loaded.");
    flash("Selected user reset.", "success");
  } else {
    flash("Reset user failed.", "error");
  }
}

async function loadAuditEventsForSelectedUser() {
  const user = requireSelectedUser();
  if (!user) {
    return;
  }

  const result = await postJson("/inspection/listAuditEvents", { userId: user.userId });
  writeResponse(ui.auditEventsResponse, result);

  if (isSuccess(result) && Array.isArray(result.body.events)) {
    renderTable(
      ui.auditEventsContainer,
      result.body.events,
      [
        ["at", "At"],
        ["type", "Type"],
        ["details", "Details"],
      ],
      formatAuditCell,
    );
  } else {
    renderEmpty(ui.auditEventsContainer, "No audit events loaded.");
  }
}

async function onGetRequestAudit(event) {
  event.preventDefault();
  await loadRequestAudit(ui.requestAuditOperationId.value.trim());
}

async function loadRequestAudit(operationId) {
  const user = requireSelectedUser();
  if (!user || !operationId) {
    return;
  }

  const result = await postJson("/inspection/getRequestAudit", {
    userId: user.userId,
    operationId,
  });

  writeResponse(ui.requestAuditResponse, result);
}

function renderAllocationInputs(containerId, prefix) {
  const container = document.getElementById(containerId);
  container.innerHTML = "";

  for (const assetCode of ASSET_CODES) {
    const row = document.createElement("label");
    row.className = "allocation-row";
    row.innerHTML = `
      <span class="allocation-label">${assetCode}</span>
      <input id="${prefix}-allocation-${assetCode}" type="number" min="0" max="100" step="1" />
    `;
    container.appendChild(row);
  }
}

function renderUnitPriceInputs() {
  const container = document.getElementById("unit-price-fields");
  container.innerHTML = "";

  for (const assetCode of ASSET_CODES) {
    const row = document.createElement("label");
    row.className = "unit-price-row";
    row.innerHTML = `
      <span class="allocation-label">${assetCode}</span>
      <input id="unit-price-${assetCode}" type="number" min="0" step="0.0001" value="${DEFAULT_PRICE}" />
    `;
    container.appendChild(row);
  }
}

function updateAllocationTotal(prefix) {
  const allocations = readAllocations(prefix, false);
  const total = allocations.reduce((sum, entry) => sum + Number(entry.percentage || 0), 0);
  const target = prefix === "create" ? ui.createTotal : ui.memberTotal;
  target.textContent = `Total: ${total}%`;
}

function readAllocations(prefix, filterZero = true) {
  const allocations = ASSET_CODES.map((assetCode) => ({
    assetCode,
    percentage: Number(document.getElementById(`${prefix}-allocation-${assetCode}`).value || 0),
  }));

  return filterZero ? allocations.filter((entry) => entry.percentage > 0) : allocations;
}

function writeAllocations(prefix, allocations) {
  const map = new Map((allocations || []).map((entry) => [entry.assetCode, entry.percentage]));

  for (const assetCode of ASSET_CODES) {
    const input = document.getElementById(`${prefix}-allocation-${assetCode}`);
    input.value = map.has(assetCode) ? String(map.get(assetCode)) : "";
  }
}

function renderTable(container, rows, columns, formatter = defaultCellFormatter) {
  if (!rows || !rows.length) {
    renderEmpty(container, "No records.");
    return;
  }

  const table = document.createElement("table");
  const thead = document.createElement("thead");
  const tbody = document.createElement("tbody");

  const headRow = document.createElement("tr");
  for (const [, label] of columns) {
    const th = document.createElement("th");
    th.textContent = label;
    headRow.appendChild(th);
  }
  thead.appendChild(headRow);

  for (const row of rows) {
    const tr = document.createElement("tr");
    for (const [key] of columns) {
      const td = document.createElement("td");
      td.innerHTML = formatter(row, key);
      tr.appendChild(td);
    }
    tbody.appendChild(tr);
  }

  table.appendChild(thead);
  table.appendChild(tbody);
  container.innerHTML = "";
  container.appendChild(table);
}

function renderEmpty(container, message) {
  container.innerHTML = `<div class="empty-state small">${escapeHtml(message)}</div>`;
}

function formatAuditCell(row, key) {
  if (key === "details") {
    return `<code>${escapeHtml(JSON.stringify(row.details || {}))}</code>`;
  }
  return defaultCellFormatter(row, key);
}

function defaultCellFormatter(row, key) {
  const value = row[key];
  if (value === null || value === undefined || value === "") {
    return '<span class="muted">-</span>';
  }
  if (typeof value === "object") {
    return `<code>${escapeHtml(JSON.stringify(value))}</code>`;
  }
  return escapeHtml(String(value));
}

async function postJson(path, payload) {
  const base = (state.apiBase || defaultApiBase()).replace(/\/$/, "");
  const url = `${base}${path}`;

  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    const text = await response.text();
    let body = {};

    if (text) {
      try {
        body = JSON.parse(text);
      } catch (error) {
        body = { raw: text, parseError: String(error) };
      }
    }

    return {
      httpStatus: response.status,
      ok: response.ok,
      body,
      url,
      payload,
    };
  } catch (error) {
    return {
      httpStatus: 0,
      ok: false,
      body: {
        ok: false,
        error: String(error),
      },
      url,
      payload,
    };
  }
}

function isSuccess(result) {
  return result.ok && result.body && result.body.ok !== false;
}

function writeResponse(element, result) {
  element.textContent = JSON.stringify(
    {
      httpStatus: result.httpStatus,
      url: result.url,
      request: result.payload,
      response: result.body,
    },
    null,
    2,
  );
}

function requireSelectedUser() {
  const user = getSelectedUser();
  if (!user) {
    flash("Select or create a user first.", "error");
    return null;
  }
  return user;
}

function getSelectedUser() {
  if (!state.selectedUserId && state.users.length) {
    state.selectedUserId = state.users[0].userId;
    saveState();
  }
  return state.users.find((user) => user.userId === state.selectedUserId) || null;
}

function upsertUser(nextUser) {
  const existing = state.users.find((entry) => entry.userId === nextUser.userId);
  if (existing) {
    Object.assign(existing, nextUser);
  } else {
    state.users.push({ ...nextUser, operations: [] });
  }
}

function recordOperation(userId, operationId, label) {
  if (!operationId) {
    return;
  }

  const user = state.users.find((entry) => entry.userId === userId);
  if (!user) {
    return;
  }

  user.operations = user.operations || [];
  const existing = user.operations.find((entry) => entry.operationId === operationId);
  if (existing) {
    existing.label = label;
    return;
  }

  user.operations.unshift({
    operationId,
    label,
    recordedAt: new Date().toISOString(),
  });
}

function clearLocalCache() {
  const confirmed = window.confirm("Clear local UI state and known users?");
  if (!confirmed) {
    return;
  }
  localStorage.removeItem(STORAGE_KEY);
  state.apiBase = defaultApiBase();
  state.users = [];
  state.selectedUserId = "";
  ui.apiBaseInput.value = state.apiBase;
  ui.createResponse.textContent = "";
  ui.updateResponse.textContent = "";
  ui.setProfileResponse.textContent = "";
  ui.portfolioResponse.textContent = "";
  ui.transactionsResponse.textContent = "";
  ui.holdingsResponse.textContent = "";
  ui.mockResponse.textContent = "";
  ui.auditEventsResponse.textContent = "";
  ui.requestAuditResponse.textContent = "";
  renderEmpty(ui.portfolioContainer, "No portfolio data loaded.");
  renderEmpty(ui.transactionsContainer, "No transaction data loaded.");
  renderEmpty(ui.holdingsContainer, "No holdings data loaded.");
  renderEmpty(ui.auditEventsContainer, "No audit events loaded.");
  renderAll();
  flash("Local cache cleared.", "success");
}

function flash(message, kind = "") {
  ui.flashMessage.textContent = message;
  ui.flashMessage.className = `flash-message ${kind}`.trim();
}

function loadState() {
  const empty = {
    apiBase: defaultApiBase(),
    users: [],
    selectedUserId: "",
  };

  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return empty;
    }
    return { ...empty, ...JSON.parse(raw) };
  } catch (_error) {
    return empty;
  }
}

function saveState() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
}

function defaultApiBase() {
  return "";
}

function escapeHtml(value) {
  return value
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}
