@extends('backend.layouts.master')
{{-- TMX-CR | Client Interior Requisition | Single Blade (Create + Edit) | Phase-1 --}}
@push('styles')
  <style>
    /* ---------- Layout ---------- */
    .cr-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 12px
    }

    .cr-card {
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .04)
    }

    .cr-card-header {
      padding: 12px 16px;
      font-weight: 700;
      border-bottom: 1px solid #eef2f7
    }

    .cr-card-body {
      padding: 16px
    }

    .cr-muted {
      color: #6b7280
    }

    /* Top info strip */
    .cr-info-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px
    }

    .cr-info-item {
      border: 1px dashed #e5e7eb;
      border-radius: 12px;
      padding: 10px 12px;
      background: #fafafa
    }

    .cr-info-item .k {
      font-size: 12px;
      color: #6b7280;
      margin-bottom: 2px
    }

    .cr-info-item .v {
      font-weight: 700;
      color: #111827;
      word-break: break-word
    }

    @media(max-width:992px) {}

    /* ---------- Horizontal swipe cards ---------- */
    .cr-strip {
      display: flex;
      gap: 12px;
      overflow-x: auto;
      padding: 2px 2px 10px;
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch
    }

    .cr-strip::-webkit-scrollbar {
      display: none
    }

    .cr-pcard {
      min-width: 220px;
      border: 2px solid #1f2937;
      border-radius: 16px;
      background: #0b0f14;
      color: #39ff14;
      padding: 12px;
      cursor: pointer;
      position: relative;
      transition: .15s ease;
      box-shadow: 0 10px 24px rgba(0, 0, 0, .22);
    }

    .cr-pcard:hover {
      box-shadow: 0 10px 22px rgba(0, 0, 0, .08)
    }

    .cr-pcard.selected {
      border-color: #7c2d12;
      box-shadow: 0 10px 24px rgba(124, 45, 18, .25)
    }

    .cr-pcard .tick {
      display: none;
      position: absolute;
      top: 8px;
      right: 8px;
      width: 24px;
      height: 24px;
      border-radius: 999px;
      background: #16a34a;
      color: #fff;
      font-weight: 800;
      font-size: 14px;
      line-height: 24px;
      text-align: center;
    }

    .cr-pcard.selected .tick {
      display: block
    }

    .cr-pcard .img {
      height: 88px;
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(57, 255, 20, .16), rgba(255, 255, 255, .05));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #39ff14;
      font-size: 12px;
      margin-bottom: 10px;
      border: 1px dashed rgba(57, 255, 20, .35)
    }

    .cr-pcard .img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block
    }

    .cr-pcard .name {
      font-weight: 800;
      font-size: 15px;
      letter-spacing: .2px;
      color: #39ff14
    }

    .cr-pcard .desc {
      font-size: 12px;
      color: #39ff14;
      opacity: .85
    }

    /* ---------- Slider arrows ---------- */
    .cr-strip-wrap {
      position: relative;
      overflow: visible
    }

    .cr-navbtn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, .14);
      background: #111827;
      color: #39ff14;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 8px 18px rgba(0, 0, 0, .35);
      z-index: 3;
    }

    .cr-navbtn:active {
      transform: scale(.98)
    }

    .cr-navbtn[disabled] {
      opacity: .45;
      box-shadow: none
    }

    .cr-nav-left {
      position: absolute;
      left: 6px;
      top: 50%;
      transform: translateY(-50%)
    }

    .cr-nav-right {
      position: absolute;
      right: 6px;
      top: 50%;
      transform: translateY(-50%)
    }

    .cr-strip {
      padding-left: 48px;
      padding-right: 48px
    }

    .cr-space-label {
      color: #fff;
    }

    /* ---------- Attachments ---------- */
    .cr-att-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap
    }

    .cr-att-preview {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px
    }

    .cr-att-thumb {
      width: 110px;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }

    .cr-att-thumb img {
      width: 100%;
      height: 86px;
      object-fit: cover;
      display: block
    }

    .cr-att-thumb .cap {
      padding: 6px 8px;
      font-size: 11px;
      color: #6b7280;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis
    }

    /* --- Desktop-only: product slider full width below category/subcategory --- */
    @media (min-width: 992px) {
      .cr-products-wrap {
        flex: 0 0 100% !important;
        max-width: 100% !important;
      }
    }
  </style>
@endpush

@section('content')
  @php
    $ctx = $ctx ?? [];
    $mode = $mode ?? 'create';
    $regId = $ctx['reg_id'] ?? null;
    $clientUserId = $ctx['client_user_id'] ?? null;
    $clientName = $ctx['client_name'] ?? null;
    $clientPhone = $ctx['client_phone'] ?? null;
    $clusterDisplay = $ctx['cluster_display'] ?? null;
    $clusterAdminPhone = $ctx['cluster_admin_phone'] ?? null;

    $canClusterRemark = $canClusterRemark ?? false;
    $canHeadOfficeRemark = $canHeadOfficeRemark ?? false;

    // Project detail old values (edit + validation)
    $vAddress = old('project_address', $ctx['project_address'] ?? '');
    $vBudget = old('project_budget', $ctx['project_budget'] ?? '');
    $vEta = old('project_eta', $ctx['project_eta'] ?? '');
    $vTotalSqft = old('project_total_sqft', $ctx['project_total_sqft'] ?? '');

    $vClusterRemark = old('cluster_member_remark', $ctx['cluster_member_remark'] ?? '');
    $vHoRemark = old('head_office_remark', $ctx['head_office_remark'] ?? '');

    // Attachments (edit + old input)
    $existingAttachments = $existingAttachments ?? ($ctx['attachments'] ?? []);
  @endphp

  <div class="cr-wrap">
    {{-- Cluster help message --}}
    <div class="alert alert-info mb-3">
      To assist you in Fill-up the Requisition, You may contact to Your Nearest Cluster Admin.
      Mobile: <strong>{{ $clusterAdminPhone ?: 'N/A' }}</strong>.
      He will give you support until your Project/Requisition Completion.
    </div>

    {{-- Top display-only info --}}
    <div class="cr-card mb-3">
      <div class="cr-card-body">
        <div class="cr-info-grid">
          <div class="cr-info-item">
            <div class="k">Reg ID</div>
            <div class="v">{{ $regId ?: '-' }}</div>
          </div>
          <div class="cr-info-item">
            <div class="k">Client User ID</div>
            <div class="v">{{ $clientUserId ?: 'Not Registered' }}</div>
          </div>
          <div class="cr-info-item">
            <div class="k">Client Name</div>
            <div class="v">{{ $clientName ?: '-' }}</div>
          </div>
          <div class="cr-info-item">
            <div class="k">Client Phone</div>
            <div class="v">{{ $clientPhone ?: '-' }}</div>
          </div>
          <div class="cr-info-item">
            <div class="k">Cluster</div>
            <div class="v">{{ $clusterDisplay ?: '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- FORM (Phase-1: Step-1 fields + Phase-1 card wiring hooks) --}}
    <form method="POST"
      action="{{ $formAction ?? route('backend.interior.interior.requisitions.store', ['company' => $companyRow->slug]) }}"
      enctype="multipart/form-data" id="crForm" novalidate>
      @csrf
      @if(!empty($formMethod) && strtoupper($formMethod) !== 'POST')
        @method($formMethod)
      @endif

      {{-- Project Details --}}
      <div class="cr-card mb-3" id="step-project">
        <div class="cr-card-header">Project Details <span class="cr-muted">(Mandatory)</span></div>
        <div class="cr-card-body">

          {{-- Server errors (top) --}}
          @if ($errors->any())
            <div class="alert alert-danger">
              <strong>Please fix the following:</strong>
              <ul class="mb-0">
                @foreach ($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="mb-3">
            <label class="form-label fw-bold">Project Location / Address <span class="text-danger">*</span></label>
            <textarea name="project_address" class="form-control @error('project_address') is-invalid @enderror" rows="2"
              required>{{ $vAddress }}</textarea>
            @error('project_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Total SQFT of the Entire Project <span
                  class="text-danger">*</span></label>
              <input type="text" name="project_total_sqft"
                class="form-control @error('project_total_sqft') is-invalid @enderror" id="project_total_sqft"
                value="{{ $vTotalSqft }}" readonly placeholder="Auto calculated">
              @error('project_total_sqft')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="form-text">Auto calculated from selected Spaces (sum of space_total_sqft).</div>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Budget (Approximate) <span class="text-danger">*</span></label>
              <input type="text" name="project_budget" class="form-control @error('project_budget') is-invalid @enderror"
                value="{{ $vBudget }}" required>
              @error('project_budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Expected Time of Delivery <span class="text-danger">*</span></label>
              <input type="date" name="project_eta" class="form-control @error('project_eta') is-invalid @enderror"
                value="{{ $vEta }}" required>
              @error('project_eta')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row">
            {{-- Cluster Member Remark --}}
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Cluster Member Remark</label>
              @if($canClusterRemark)
                <textarea name="cluster_member_remark"
                  class="form-control @error('cluster_member_remark') is-invalid @enderror"
                  rows="2">{{ $vClusterRemark }}</textarea>
                @error('cluster_member_remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
              @else
                <div class="form-control bg-light" style="min-height:74px">{{ $vClusterRemark ?: '-' }}</div>
              @endif
            </div>

            {{-- Head Office Remark --}}
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Head Office Remark</label>
              @if($canHeadOfficeRemark)
                <textarea name="head_office_remark" class="form-control @error('head_office_remark') is-invalid @enderror"
                  rows="2">{{ $vHoRemark }}</textarea>
                @error('head_office_remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
              @else
                <div class="form-control bg-light" style="min-height:74px">{{ $vHoRemark ?: '-' }}</div>
              @endif
            </div>
          </div>

          {{-- Attachments (Optional) — MUST be after Head Office Remark --}}
          <div class="cr-card mt-2" style="border-radius:14px;border-style:dashed">
            <div class="cr-card-header">Client Attachments (Optional)</div>
            <div class="cr-card-body">
              <input type="file" id="crAttachments" name="client_attachments[]" accept="image/*" multiple class="d-none">
              <div class="cr-att-actions">
                <button type="button" class="btn btn-outline-primary" id="btnAddMoreImages">
                  Add More Images
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnClearImages">
                  Clear Selection
                </button>
              </div>
              <div class="form-text mt-2">
                You can select multiple images from different folders: click <strong>Add More Images</strong> multiple
                times and pick images each time.
                Allowed: common image types. Max size: <strong>10MB per file</strong>.
              </div>
              @error('client_attachments')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
              @error('client_attachments.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

              <div class="cr-att-preview" id="attPreview">
                {{-- Existing attachments preview (edit) --}}
                @if(!empty($existingAttachments) && is_iterable($existingAttachments))
                  @foreach($existingAttachments as $a)
                    @php
                      $url = $a['url'] ?? $a['file_url'] ?? $a['path_url'] ?? null;
                      $cap = $a['original_name'] ?? $a['file_name'] ?? basename((string) $url);
                    @endphp
                    @if($url)
                      <div class="cr-att-thumb">
                        <img src="{{ $url }}" alt="attachment">
                        <div class="cap" title="{{ $cap }}">{{ $cap }}</div>
                      </div>
                    @endif
                  @endforeach
                @endif
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- Project Type --}}
      <div class="cr-card mb-3" id="layer-type">
        <div class="cr-card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold">Project Type <span class="text-danger">*</span></div>
            <div class="text-muted small">Swipe horizontally</div>
          </div>
          @error('project_type_id')
            <div class="text-danger small mt-2" data-error-for="project_type_id">{{ $message }}</div>
          @enderror
          <div class="cr-strip-wrap mt-2">
            <button type="button" class="cr-navbtn cr-nav-left" id="typePrev" aria-label="Prev">&lt;</button>
            <div class="cr-strip pad-arrows" id="stripType"></div>
            <button type="button" class="cr-navbtn cr-nav-right" id="typeNext" aria-label="Next">&gt;</button>
          </div>
          <input type="hidden" name="project_type_id" id="project_type_id"
            value="{{ old('project_type_id', $ctx['project_type_id'] ?? '') }}">
        </div>
      </div>

      {{-- Project Sub-Type --}}
      <div class="cr-card mb-3" id="layer-subtype">
        <div class="cr-card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold">Project Sub-Type <span class="text-danger">*</span></div>
            <div class="text-muted small">Depends on Project Type</div>
          </div>
          @error('project_subtype_id')
            <div class="text-danger small mt-2" data-error-for="project_subtype_id">{{ $message }}</div>
          @enderror
          <div class="cr-strip mt-2" id="stripSubtype"></div>
          <input type="hidden" name="project_subtype_id" id="project_subtype_id"
            value="{{ old('project_subtype_id', $ctx['project_subtype_id'] ?? '') }}">
        </div>
      </div>

      {{-- Spaces --}}
      <div class="cr-card mb-3" id="layer-space">
        <div class="cr-card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="fw-bold">Spaces to be Included <span class="text-danger">*</span></div>
            <div class="text-muted small">At least one</div>
          </div>
          @error('spaces')
            <div class="text-danger small mt-2" data-error-for="spaces">{{ $message }}</div>
          @enderror
          <div class="cr-strip-wrap mt-2">
            <button type="button" class="cr-navbtn cr-nav-left" id="spacePrev" aria-label="Prev">&lt;</button>
            <div class="cr-strip pad-arrows" id="stripSpace"></div>
            <button type="button" class="cr-navbtn cr-nav-right" id="spaceNext" aria-label="Next">&gt;</button>
          </div>
        </div>
      </div>

      {{-- Space-wise Categories & Products --}}
      <div class="cr-card mb-3" id="layer-category">
        <div class="cr-card-body">
          <div class="fw-bold mb-2">Space-wise Categories &amp; Products</div>
          <div id="crSpaceDetails"></div>
        </div>
      </div>

      {{-- Sub-Categories (hidden, kept for layout parity) --}}
      <div class="cr-card mb-3 d-none" id="layer-subcategory">
        <div class="cr-card-body">
          <div class="fw-bold">Sub-Categories</div>
          <div class="cr-strip mt-2" id="stripSubcategory"></div>
        </div>
      </div>

      {{-- Products (hidden, kept for layout parity) --}}
      <div class="cr-card mb-3 d-none" id="layer-product">
        <div class="cr-card-body">
          <div class="fw-bold">Products</div>
          <div class="cr-strip mt-2" id="stripProduct"></div>
        </div>
      </div>

      <input type="hidden" name="state_json" id="state_json" value="">

      <div class="mt-4 d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary" id="btnSubmit">Save / Submit</button>
      </div>

  
    

    </form>
  </div>

  @push('scripts')
    <script>
      (function () {
        // ---------- Left nav active state ----------
        const sections = ['layer-type', 'layer-subtype', 'layer-space', 'layer-category', 'layer-subcategory', 'layer-product']
          .map(id => document.getElementById(id)).filter(Boolean);

        // Helper to clear server-side error blocks above strips
        function clearFieldErrors(names) {
          if (!Array.isArray(names)) names = [names];
          names.forEach(function (name) {
            document.querySelectorAll('[data-error-for="' + name + '"]').forEach(function (el) {
              el.remove();
            });
          });
        }

        function setActive(id) {
          navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('data-nav') === id));
        }
        const io = new IntersectionObserver((entries) => {
          entries.forEach(e => {
            if (e.isIntersecting) { setActive(e.target.id); }
          });
        }, { root: null, threshold: 0.4 });
        sections.forEach(s => io.observe(s));

        // ---------- Attachments: Add More Images + Clear Selection ----------
        const input = document.getElementById('crAttachments');
        const btnAdd = document.getElementById('btnAddMoreImages');
        const btnClear = document.getElementById('btnClearImages');
        const preview = document.getElementById('attPreview');

        let dt = new DataTransfer();

        function renderPreview() {
          // Keep server-rendered thumbs (edit) if user hasn't selected anything new
          // If user selects new files, we show ONLY newly selected files (clear existing preview)
          preview.innerHTML = '';
          Array.from(dt.files).forEach((f) => {
            const wrap = document.createElement('div');
            wrap.className = 'cr-att-thumb';
            const img = document.createElement('img');
            const cap = document.createElement('div');
            cap.className = 'cap';
            cap.title = f.name;
            cap.textContent = f.name;

            wrap.appendChild(img);
            wrap.appendChild(cap);
            preview.appendChild(wrap);

            const reader = new FileReader();
            reader.onload = (ev) => { img.src = ev.target.result; };
            reader.readAsDataURL(f);
          });
        }

        btnAdd?.addEventListener('click', () => input.click());

        input?.addEventListener('change', () => {
          const files = Array.from(input.files || []);
          if (!files.length) return;

          // Merge into DataTransfer to preserve previous selections
          files.forEach(f => dt.items.add(f));
          input.files = dt.files;
          renderPreview();
        });

        btnClear?.addEventListener('click', () => {
          dt = new DataTransfer();
          input.value = '';
          preview.innerHTML = '';
        });

        // ---------- Project Type layer (Phase-1) ----------
        const INIT_STATE = @json($state ?? null);
        const projectTypes = @json($projectTypes ?? []);
        const stripType = document.getElementById('stripType');
        const typeId = document.getElementById('project_type_id');
        const typePrev = document.getElementById('typePrev');
        const typeNext = document.getElementById('typeNext');

        function normalizeImg(p) {
          if (!p) return '';
          const s = String(p);
          if (/^https?:\/\//i.test(s)) return s;
          if (s.startsWith('/')) return s;
          if (s.startsWith('storage/')) return '/' + s;
          return '/storage/' + s.replace(/^\/+/, '');
        }

        function clearDescendantLayers() {
          // descendant containers remain blank for now
          document.getElementById('stripSubtype')?.replaceChildren();
          document.getElementById('stripSpace')?.replaceChildren();
          document.getElementById('stripCategory')?.replaceChildren();
          document.getElementById('stripSubcategory')?.replaceChildren();
          document.getElementById('stripProduct')?.replaceChildren();
          const totalEl = document.getElementById('project_total_sqft');
          if (totalEl) totalEl.value = '';
          // hidden ids (future)
          const hid = ['project_subtype_id', 'selected_spaces_json', 'selected_categories_json', 'selected_subcategories_json', 'selected_products_json'];
          hid.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        }


        function clearAfterSubtype() {
          const stripSpaceEl = document.getElementById('stripSpace');
          if (stripSpaceEl) { stripSpaceEl.replaceChildren(); }

          document.getElementById('stripCategory')?.replaceChildren();
          document.getElementById('stripSubcategory')?.replaceChildren();
          document.getElementById('stripProduct')?.replaceChildren();

          const totalEl = document.getElementById('project_total_sqft');
          if (totalEl) totalEl.value = '';

          const hiddenIds = ['selected_spaces_json', 'selected_categories_json', 'selected_subcategories_json', 'selected_products_json'];
          hiddenIds.forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
          });
        }

        function setSelectedType(newId) {
          if (!typeId) return;
          const current = String(typeId.value || '');
          const incoming = newId == null ? '' : String(newId);

          // deselect if same
          if (incoming && incoming === current) {
            typeId.value = '';
            stripType?.querySelectorAll('.cr-pcard.selected').forEach(c => c.classList.remove('selected'));
            clearDescendantLayers();
            return;
          }

          // select one
          typeId.value = incoming;
          stripType?.querySelectorAll('.cr-pcard').forEach(c => {
            c.classList.toggle('selected', c.dataset.id === incoming);
          });
          // when switching type, clear descendants (will be rehydrated in future passes)
          clearDescendantLayers();
        }

        function renderProjectTypes() {
          if (!stripType) return;
          stripType.innerHTML = '';

          (projectTypes || []).forEach(row => {
            const id = row.id;
            const name = row.name ?? row.title ?? ('Type #' + id);
            const desc = row.description ?? '';
            const img = normalizeImg(row.card_image_path ?? row.main_image_url ?? row.image_path ?? '');

            const el = document.createElement('div');
            el.className = 'cr-pcard';
            el.dataset.id = String(id);
            el.setAttribute('role', 'button');
            el.setAttribute('tabindex', '0');

            const imgWrap = document.createElement('div');
            imgWrap.className = 'img';
            if (img) {
              const imgEl = document.createElement('img');
              imgEl.src = img;
              imgEl.alt = name;
              imgWrap.appendChild(imgEl);
            }

            const title = document.createElement('div');
            title.className = 'name';
            title.textContent = name;

            const d = document.createElement('div');
            d.className = 'desc';
            d.textContent = desc;

            const tick = document.createElement('div');
            tick.className = 'tick';
            tick.textContent = '✓';

            el.appendChild(imgWrap);
            el.appendChild(title);
            if (desc) el.appendChild(d);
            el.appendChild(tick);

            el.addEventListener('click', () => setSelectedType(id));
            el.addEventListener('keydown', (ev) => {
              if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                setSelectedType(id);
              }
            });

            stripType.appendChild(el);
          });

          // apply selection if already set (edit mode)
          if (typeId && typeId.value) {
            setSelectedType(typeId.value);
          }
        }


        typePrev?.addEventListener('click', () => { stripType?.scrollBy({ left: -260, behavior: 'smooth' }); });
        typeNext?.addEventListener('click', () => { stripType?.scrollBy({ left: 260, behavior: 'smooth' }); });

        renderProjectTypes();

        // ---------- Project Subtype layer (Phase-2) ----------
        const projectSubtypes = @json($projectSubtypes ?? []);
        const stripSubtype = document.getElementById('stripSubtype');
        const subtypeId = document.getElementById('project_subtype_id');

        // ---------- Spaces layer (Phase-3) ----------
        const spacesData = @json($spaces ?? []);
        const stripSpace = document.getElementById('stripSpace');
        const spacePrev = document.getElementById('spacePrev');
        const spaceNext = document.getElementById('spaceNext');
        const projectTotalSqftInput = document.getElementById('project_total_sqft');

        spacePrev?.addEventListener('click', () => { stripSpace?.scrollBy({ left: -260, behavior: 'smooth' }); });
        spaceNext?.addEventListener('click', () => { stripSpace?.scrollBy({ left: 260, behavior: 'smooth' }); });

        function updateProjectTotalSqft() {
          if (!projectTotalSqftInput) return;
          let sum = 0;
          document.querySelectorAll('#stripSpace .cr-pcard.selected .space-sqft').forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) { sum += val; }
          });
          projectTotalSqftInput.value = sum > 0 ? sum.toFixed(2) : '';
        }

        function hydrateSpaceFromState(id, qtyInput, sqftInput) {
          if (!INIT_STATE || !INIT_STATE.spaces) return false;
          const key = String(id);
          const s = INIT_STATE.spaces[key];
          if (!s) return false;
          const hQty = Number(s.qty ?? s.space_qty ?? qtyInput.value);
          const hSqft = Number(s.sqft ?? s.space_total_sqft ?? sqftInput.value);
          if (!isNaN(hQty) && hQty > 0) qtyInput.value = hQty;
          if (!isNaN(hSqft) && hSqft >= 0) sqftInput.value = hSqft;
          return true;
        }

        function renderSpacesForSubtype(typeSubtypeId) {
          if (!stripSpace) return;
          stripSpace.innerHTML = '';
          if (!typeSubtypeId) {
            updateProjectTotalSqft();
            return;
          }
          const filtered = (spacesData || []).filter(r => String(r.project_subtype_id) === String(typeSubtypeId));

          filtered.forEach(row => {
            const id = row.id;
            const name = row.name ?? ('Space #' + id);
            const desc = row.description ?? '';
            const img = normalizeImg(row.card_image_path ?? '');

            const card = document.createElement('div');
            card.className = 'cr-pcard';
            card.dataset.id = String(id);

            const imgWrap = document.createElement('div');
            imgWrap.className = 'img';
            if (img) {
              const im = document.createElement('img');
              im.src = img;
              im.alt = name;
              imgWrap.appendChild(im);
            }

            const title = document.createElement('div'); title.className = 'name'; title.textContent = name;
            const d = document.createElement('div'); d.className = 'desc'; d.textContent = desc;
            const tick = document.createElement('div'); tick.className = 'tick'; tick.textContent = '✓';

            card.appendChild(imgWrap);
            card.appendChild(title);
            if (desc) card.appendChild(d);
            card.appendChild(tick);

            const meta = document.createElement('div');
            meta.className = 'mt-2';

            const namePrefix = 'spaces[' + id + ']';

            const qtyLabel = document.createElement('div');
            qtyLabel.className = 'cr-space-label small mb-1';
            qtyLabel.textContent = 'Qty';

            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.min = '1';
            qtyInput.step = '1';
            qtyInput.className = 'form-control form-control-sm mb-1 space-qty';
            const dQty = Number(row.default_quantity ?? 1);
            qtyInput.value = (!isNaN(dQty) && dQty > 0) ? dQty : 1;

            const sqftInput = document.createElement('input');
            sqftInput.type = 'number';
            sqftInput.min = '0';
            sqftInput.step = '0.01';
            sqftInput.className = 'form-control form-control-sm space-sqft';
            const dSqft = Number(row.default_area_sqft ?? 0);
            sqftInput.value = !isNaN(dSqft) && dSqft >= 0 ? dSqft : 0;

            const hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.className = 'space-id-hidden';

            function applyNames(selected) {
              if (selected) {
                hiddenId.name = namePrefix + '[space_id]';
                hiddenId.value = id;
                qtyInput.name = namePrefix + '[qty]';
                sqftInput.name = namePrefix + '[sqft]';
              } else {
                hiddenId.name = '';
                hiddenId.value = '';
                qtyInput.name = '';
                sqftInput.name = '';
              }
            }

            function setSelected(selected) {
              card.classList.toggle('selected', selected);
              applyNames(selected);
              updateProjectTotalSqft();
              if (document.querySelectorAll('#stripSpace .cr-pcard.selected').length > 0) {
                clearFieldErrors('spaces');
              }
              // keep Space-wise blocks in sync with selected spaces
              if (typeof syncSpaceBlocks === 'function') {
                syncSpaceBlocks();
              }
            }

            const hydrated = hydrateSpaceFromState(id, qtyInput, sqftInput);
            if (hydrated) {
              setSelected(true);
            }

            card.addEventListener('click', function (ev) {
              if (ev.target === qtyInput || ev.target === sqftInput) return;
              const nowSelected = !card.classList.contains('selected');
              setSelected(nowSelected);
              if (!nowSelected) {
                const anySelected = stripSpace?.querySelector('.cr-pcard.selected');
                if (!anySelected) {
                  document.getElementById('stripCategory')?.replaceChildren();
                  document.getElementById('stripSubcategory')?.replaceChildren();
                  document.getElementById('stripProduct')?.replaceChildren();
                }
              }
            });

            qtyInput.addEventListener('input', function () {
              if (card.classList.contains('selected')) updateProjectTotalSqft();
            });

            sqftInput.addEventListener('input', function () {
              if (card.classList.contains('selected')) updateProjectTotalSqft();
            });

            const sqftLabel = document.createElement('div');
            sqftLabel.className = 'cr-space-label small mb-1';
            sqftLabel.textContent = 'Total SQFT';

            meta.appendChild(qtyLabel);
            meta.appendChild(qtyInput);
            meta.appendChild(sqftLabel);
            meta.appendChild(sqftInput);
            meta.appendChild(hiddenId);
            card.appendChild(meta);

            stripSpace.appendChild(card);
          });

          updateProjectTotalSqft();
          if (typeof syncSpaceBlocks === 'function') {
            syncSpaceBlocks();
          }
        }

        function renderProjectSubtypes(typeID) {
          if (!stripSubtype) return;
          stripSubtype.innerHTML = '';
          if (!typeID) return;

          (projectSubtypes || []).filter(r => String(r.project_type_id) === String(typeID))
            .forEach(row => {
              const id = row.id;
              const name = row.name ?? ('Subtype #' + id);
              const desc = row.description ?? '';
              const img = normalizeImg(row.card_image_path ?? '');

              const el = document.createElement('div');
              el.className = 'cr-pcard';
              el.dataset.id = String(id);

              const imgWrap = document.createElement('div');
              imgWrap.className = 'img';
              if (img) {
                const im = document.createElement('img');
                im.src = img; im.alt = name;
                imgWrap.appendChild(im);
              }

              const title = document.createElement('div'); title.className = 'name'; title.textContent = name;
              const d = document.createElement('div'); d.className = 'desc'; d.textContent = desc;
              const tick = document.createElement('div'); tick.className = 'tick'; tick.textContent = '✓';

              el.appendChild(imgWrap); el.appendChild(title);
              if (desc) el.appendChild(d);
              el.appendChild(tick);

              el.addEventListener('click', () => {
                if (subtypeId.value == id) {
                  subtypeId.value = '';
                  stripSubtype.querySelectorAll('.cr-pcard.selected').forEach(c => c.classList.remove('selected'));
                  clearAfterSubtype();
                  clearFieldErrors(['project_subtype_id', 'spaces']);
                  return;
                }
                subtypeId.value = id;
                stripSubtype.querySelectorAll('.cr-pcard.selected').forEach(c => c.classList.remove('selected'));
                el.classList.add('selected');
                renderSpacesForSubtype(id);
                clearFieldErrors(['project_subtype_id', 'spaces']);
              });

              stripSubtype.appendChild(el);
            });

          if (subtypeId && subtypeId.value) {
            const sel = stripSubtype.querySelector('.cr-pcard[data-id="' + subtypeId.value + '"]');
            if (sel) sel.classList.add('selected');
            renderSpacesForSubtype(subtypeId.value);
          } else {
            renderSpacesForSubtype('');
          }
        }

        // hook into type selection
        const origSetSelectedType = setSelectedType;
        setSelectedType = function (newId) {
          origSetSelectedType(newId);
          // Clear cascading error messages once user interacts with Project Type
          clearFieldErrors(['project_type_id', 'project_subtype_id', 'spaces']);

          // After origSetSelectedType, rely on FINAL typeId state, not raw newId
          const effectiveTypeId = (typeId && typeId.value) ? typeId.value : '';
          subtypeId.value = '';
          renderProjectSubtypes(effectiveTypeId);
        };


        // ---------- Client-side validation (Phase-1 mandatory fields) ----------

        // ---------- Space-wise Category/Subcategory/Product rows (Step-2) ----------
        const spaceDetailsWrap = document.getElementById('crSpaceDetails');
        const apiCategoriesUrl = @json(url('backend/interior/' . $companyRow->slug . '/requisitions/api/categories'));
        const apiSubcategoriesUrl = @json(url('backend/interior/' . $companyRow->slug . '/requisitions/api/subcategories'));
        const apiProductsUrl = @json(url('backend/interior/' . $companyRow->slug . '/requisitions/api/products'));

        const spaceNameMap = {};
        (spacesData || []).forEach(function (row) {
          if (row && row.id != null) {
            spaceNameMap[String(row.id)] = row.name ?? ('Space #' + row.id);
          }
        });

        const categoryCache = {};
        const subcategoryCache = {};
        const productCache = {};

        function fetchCategoriesForSpace(spaceId) {
          const key = String(spaceId);
          if (categoryCache[key]) return Promise.resolve(categoryCache[key]);
          const url = apiCategoriesUrl + '?space_id=' + encodeURIComponent(spaceId);
          return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : [])
            .then(json => {
              const rows = Array.isArray(json) ? json : [];
              categoryCache[key] = rows;
              return rows;
            })
            .catch(() => []);
        }

        function fetchSubcategoriesForCategory(categoryId) {
          const key = String(categoryId);
          if (subcategoryCache[key]) return Promise.resolve(subcategoryCache[key]);
          const url = apiSubcategoriesUrl + '?category_id=' + encodeURIComponent(categoryId);
          return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : [])
            .then(json => {
              const rows = Array.isArray(json) ? json : [];
              subcategoryCache[key] = rows;
              return rows;
            })
            .catch(() => []);
        }

        function fetchProductsForSubcategory(subcategoryId) {
          const key = String(subcategoryId);
          if (productCache[key]) return Promise.resolve(productCache[key]);
          const url = apiProductsUrl + '?subcategory_id=' + encodeURIComponent(subcategoryId);
          return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : [])
            .then(json => {
              const rows = Array.isArray(json) ? json : [];
              productCache[key] = rows;
              return rows;
            })
            .catch(() => []);
        }

        function buildProductCards(rowEl, products, existing) {
          const wrap = rowEl.querySelector('.js-products-wrap');
          const strip = rowEl.querySelector('.js-products-strip');
          if (!wrap || !strip) {
            return;
          }
          strip.innerHTML = '';
          const hasProducts = Array.isArray(products) && products.length > 0;
          wrap.classList.toggle('d-none', !hasProducts);
          if (!hasProducts) return;

          const existingProducts = existing && existing.products ? existing.products : {};

          products.forEach(function (p) {
            const pid = p.id;
            const name = p.name ?? ('Product #' + pid);
            const desc = p.short_description ?? '';
            const img = normalizeImg(p.card_image_path ?? '');

            const card = document.createElement('div');
            card.className = 'cr-pcard js-product-card';
            card.dataset.productId = String(pid);

            const imgWrap = document.createElement('div');
            imgWrap.className = 'img';
            if (img) {
              const im = document.createElement('img');
              im.src = img;
              im.alt = name;
              imgWrap.appendChild(im);
            }

            const title = document.createElement('div');
            title.className = 'name';
            title.textContent = name;

            const d = document.createElement('div');
            d.className = 'desc';
            d.textContent = desc;

            const tick = document.createElement('div');
            tick.className = 'tick';
            tick.textContent = '✓';

            const meta = document.createElement('div');
            meta.className = 'mt-2';

            const qtyLabel = document.createElement('div');
            qtyLabel.className = 'cr-space-label small mb-1';
            qtyLabel.textContent = 'Qty';

            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.min = '1';
            qtyInput.step = '1';
            qtyInput.className = 'form-control form-control-sm js-prod-qty';
            const existingQty = existingProducts && existingProducts[pid] ? Number(existingProducts[pid]) : 1;
            qtyInput.value = (!isNaN(existingQty) && existingQty > 0) ? existingQty : 1;

            meta.appendChild(qtyLabel);
            meta.appendChild(qtyInput);

            card.appendChild(imgWrap);
            card.appendChild(title);
            if (desc) card.appendChild(d);
            card.appendChild(tick);
            card.appendChild(meta);

            if (existingProducts && Object.prototype.hasOwnProperty.call(existingProducts, pid)) {
              card.classList.add('selected');
            }

            card.addEventListener('click', function (ev) {
              if (ev.target && ev.target.classList.contains('js-prod-qty')) {
                return;
              }
              card.classList.toggle('selected');
            });

            strip.appendChild(card);
          });

          const prevBtn = rowEl.querySelector('.js-products-prev');
          const nextBtn = rowEl.querySelector('.js-products-next');
          prevBtn?.addEventListener('click', function () {
            strip.scrollBy({ left: -260, behavior: 'smooth' });
          });
          nextBtn?.addEventListener('click', function () {
            strip.scrollBy({ left: 260, behavior: 'smooth' });
          });
        }

        function loadCategoriesForRow(rowEl, existing) {
          const spaceId = rowEl.dataset.spaceId;
          const catSelect = rowEl.querySelector('.js-category');
          const subSelect = rowEl.querySelector('.js-subcategory');
          if (!spaceId || !catSelect || !subSelect) return;

          catSelect.innerHTML = '<option value="">-- Select Category --</option>';
          subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';

          fetchCategoriesForSpace(spaceId).then(function (rows) {
            rows.forEach(function (r) {
              const opt = document.createElement('option');
              opt.value = String(r.id);
              opt.textContent = r.name ?? ('Category #' + r.id);
              catSelect.appendChild(opt);
            });

            const existingCatId = existing && existing.categoryId ? String(existing.categoryId) : '';
            if (existingCatId) {
              catSelect.value = existingCatId;
              loadSubcategoriesForRow(rowEl, existingCatId, existing);
            }
          });

          catSelect.addEventListener('change', function () {
            const cid = catSelect.value;
            subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
            rowEl.querySelector('.js-products-wrap')?.classList.add('d-none');
            if (!cid) return;
            loadSubcategoriesForRow(rowEl, cid, null);
          });
        }

        function loadSubcategoriesForRow(rowEl, categoryId, existing) {
          const subSelect = rowEl.querySelector('.js-subcategory');
          if (!subSelect) return;
          subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';

          fetchSubcategoriesForCategory(categoryId).then(function (rows) {
            rows.forEach(function (r) {
              const opt = document.createElement('option');
              opt.value = String(r.id);
              opt.textContent = r.name ?? ('Subcategory #' + r.id);
              subSelect.appendChild(opt);
            });

            const existingSubId = existing && existing.subcategoryId ? String(existing.subcategoryId) : '';
            if (existingSubId) {
              subSelect.value = existingSubId;
              loadProductsForRow(rowEl, existing);
            }
          });

          subSelect.addEventListener('change', function () {
            const sid = subSelect.value;
            if (!sid) {
              rowEl.querySelector('.js-products-wrap')?.classList.add('d-none');
              return;
            }
            loadProductsForRow(rowEl, null);
          });
        }

        function loadProductsForRow(rowEl, existing) {
          const subSelect = rowEl.querySelector('.js-subcategory');
          if (!subSelect) return;
          const subId = subSelect.value;
          if (!subId) return;

          fetchProductsForSubcategory(subId).then(function (rows) {
            buildProductCards(rowEl, rows, existing);
          });
        }

        function addSpaceRow(spaceId, rowsWrap, existing) {
          const row = document.createElement('div');
          row.className = 'row align-items-start g-2 mb-2 cr-space-row';
          row.dataset.spaceId = String(spaceId);

          row.innerHTML = `
          <div class="col-md-3">
            <select class="form-select form-select-sm js-category">
              <option value="">-- Select Category --</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm js-subcategory">
              <option value="">-- Select Subcategory --</option>
            </select>
          </div>
          <div class="col-12 mt-1">
            <div class="cr-strip-wrap js-products-wrap d-none">
              <button type="button" class="cr-navbtn cr-nav-left js-products-prev" aria-label="Prev">&lt;</button>
              <div class="cr-strip pad-arrows js-products-strip"></div>
              <button type="button" class="cr-navbtn cr-nav-right js-products-next" aria-label="Next">&gt;</button>
            </div>
          </div>
          <div class="col-md-2 d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-danger js-row-remove">Delete</button>
          </div>
        `;

          rowsWrap.appendChild(row);

          const btnRemove = row.querySelector('.js-row-remove');
          btnRemove?.addEventListener('click', function () {
            row.remove();
          });

          loadCategoriesForRow(row, existing);
        }

        function buildSpaceBlock(spaceId, existing) {
          if (!spaceDetailsWrap) return;
          const spaceKey = String(spaceId);
          const existingBlock = spaceDetailsWrap.querySelector('.cr-space-block[data-space-id="' + spaceKey + '"]');
          if (existingBlock) {
            return;
          }

          const block = document.createElement('div');
          block.className = 'border rounded p-2 mb-3 cr-space-block';
          block.dataset.spaceId = spaceKey;

          const header = document.createElement('div');
          header.className = 'd-flex justify-content-between align-items-center mb-2';

          const title = document.createElement('div');
          title.className = 'fw-bold';
          title.textContent = spaceNameMap[spaceKey] || ('Space #' + spaceKey);

          const btnAdd = document.createElement('button');
          btnAdd.type = 'button';
          btnAdd.className = 'btn btn-sm btn-outline-primary';
          btnAdd.textContent = 'Add Row';

          header.appendChild(title);
          header.appendChild(btnAdd);

          const rowsWrap = document.createElement('div');
          rowsWrap.className = 'space-rows';
          rowsWrap.dataset.spaceId = spaceKey;

          block.appendChild(header);
          block.appendChild(rowsWrap);

          spaceDetailsWrap.appendChild(block);

          const existingRows = existing && existing.rows ? existing.rows : null;
          if (existingRows && Array.isArray(existingRows) && existingRows.length) {
            existingRows.forEach(function (er) {
              addSpaceRow(spaceId, rowsWrap, er);
            });
          } else {
            addSpaceRow(spaceId, rowsWrap, null);
          }

          btnAdd.addEventListener('click', function () {
            addSpaceRow(spaceId, rowsWrap, null);
          });
        }

        function syncSpaceBlocks() {
          if (!spaceDetailsWrap) return;
          const selectedSpaces = Array.from(document.querySelectorAll('#stripSpace .cr-pcard.selected'))
            .map(function (card) { return card.dataset.id; })
            .filter(function (id) { return !!id; });

          const existingBlocks = Array.from(spaceDetailsWrap.querySelectorAll('.cr-space-block'));
          existingBlocks.forEach(function (block) {
            const sid = block.dataset.spaceId;
            if (!selectedSpaces.includes(sid)) {
              block.remove();
            }
          });

          selectedSpaces.forEach(function (sid) {
            buildSpaceBlock(sid, null);
          });
        }

        function buildStateFromUI() {
          const state = {
            project_type_id: typeId ? Number(typeId.value || 0) : 0,
            project_subtype_id: subtypeId ? Number(subtypeId.value || 0) : 0,
            spaces: {}
          };

          document.querySelectorAll('#stripSpace .cr-pcard').forEach(function (card) {
            const id = card.dataset.id;
            if (!id) return;
            const key = String(id);
            const selected = card.classList.contains('selected');
            if (!selected) return;

            const qtyInput = card.querySelector('.space-qty');
            const sqftInput = card.querySelector('.space-sqft');

            let qty = qtyInput ? Number(qtyInput.value) : 1;
            if (!qty || qty < 1) qty = 1;

            let sqft = sqftInput ? Number(sqftInput.value) : 0;
            if (!sqft || sqft < 0) sqft = 0;

            state.spaces[key] = {
              qty: qty,
              sqft: sqft,
              categories: {}
            };
          });

          const blocks = document.querySelectorAll('.cr-space-block');
          blocks.forEach(function (block) {
            const spaceId = block.dataset.spaceId;
            if (!spaceId || !state.spaces[spaceId]) return;

            const rowsWrap = block.querySelector('.space-rows');
            if (!rowsWrap) return;
            const rows = Array.from(rowsWrap.querySelectorAll('.cr-space-row'));
            rows.forEach(function (row) {
              const catSelect = row.querySelector('.js-category');
              const subSelect = row.querySelector('.js-subcategory');
              if (!catSelect || !subSelect) return;
              const cid = catSelect.value;
              const sid = subSelect.value;
              if (!cid || !sid) return;

              if (!state.spaces[spaceId].categories[cid]) {
                state.spaces[spaceId].categories[cid] = { subcategories: {} };
              }
              if (!state.spaces[spaceId].categories[cid].subcategories[sid]) {
                state.spaces[spaceId].categories[cid].subcategories[sid] = { products: {} };
              }
              const bucket = state.spaces[spaceId].categories[cid].subcategories[sid].products;

              const prodCards = row.querySelectorAll('.js-product-card.selected');
              prodCards.forEach(function (pc) {
                const pid = pc.dataset.productId;
                if (!pid) return;
                const qtyInput = pc.querySelector('.js-prod-qty');
                let q = qtyInput ? Number(qtyInput.value) : 1;
                if (!q || q < 1) q = 1;
                bucket[pid] = q;
              });
            });
          });

          return state;
        }

        const form = document.getElementById('crForm');
        form?.addEventListener('submit', function (e) {
          const addr = form.querySelector('[name="project_address"]');
          const budget = form.querySelector('[name="project_budget"]');
          const eta = form.querySelector('[name="project_eta"]');
          const typeId = document.getElementById('project_type_id');
          const subtypeId = document.getElementById('project_subtype_id');

          // spaces: expect checkboxes named spaces[] or hidden JSON; keep minimal here
          const spaceSelected = form.querySelectorAll('[name="spaces[]"]:checked').length > 0
            || (document.querySelectorAll('#stripSpace .cr-pcard.selected').length > 0);

          let firstBad = null;

          function mark(el, ok) {
            if (!el) return;
            if (ok) { el.classList.remove('is-invalid'); }
            else { el.classList.add('is-invalid'); firstBad = firstBad || el; }
          }

          mark(addr, !!(addr && addr.value.trim()));
          mark(budget, !!(budget && budget.value.trim()));
          mark(eta, !!(eta && eta.value));
          mark(typeId, !!(typeId && typeId.value));
          mark(subtypeId, !!(subtypeId && subtypeId.value));

          if (!spaceSelected) {
            // soft alert (Bootstrap)
            const existing = document.getElementById('crSpaceErr');
            if (!existing) {
              const div = document.createElement('div');
              div.id = 'crSpaceErr';
              div.className = 'alert alert-danger mt-2';
              div.textContent = 'Please select at least one Space.';
              document.getElementById('layer-space')?.appendChild(div);
            }
            firstBad = firstBad || document.getElementById('layer-space');
          } else {
            const existing = document.getElementById('crSpaceErr');
            if (existing) existing.remove();
          }

          if (firstBad) {
            e.preventDefault();
            firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        });

        // Descendant layers will be implemented in the next passes.
      })();
    </script>
  @endpush
@endsection