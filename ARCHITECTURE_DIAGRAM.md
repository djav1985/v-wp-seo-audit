# Architecture Overview: Internal Domain Analysis API

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACES                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Shortcode   │  │  REST API    │  │ PHP Helper   │          │
│  │  (AJAX)      │  │  (External)  │  │ (Internal)   │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                  │                  │                   │
│         │                  │                  │                   │
└─────────┼──────────────────┼──────────────────┼──────────────────┘
          │                  │                  │
          v                  v                  v
┌─────────────────────────────────────────────────────────────────┐
│                     SERVICE LAYER (NEW)                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│              V_WPSA_Report_Service::prepare_report()            │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  • Domain validation                                    │    │
│  │  • Cache checking                                       │    │
│  │  • Analysis triggering                                  │    │
│  │  • PDF generation                                       │    │
│  │  • JSON payload assembly                                │    │
│  │  • Error handling                                       │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────┬────────────────────┬───────────────────┬──────────────┘
          │                    │                   │
          v                    v                   v
┌─────────────────────────────────────────────────────────────────┐
│                      EXISTING CORE SYSTEM                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ V_WPSA_DB    │  │ V_WPSA_      │  │ V_WPSA_      │          │
│  │              │  │ Validation   │  │ Report_      │          │
│  │ • Database   │  │              │  │ Generator    │          │
│  │ • Analysis   │  │ • Domain     │  │              │          │
│  │ • Queries    │  │   Checks     │  │ • HTML       │          │
│  │              │  │ • Format     │  │ • PDF        │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                  │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               v
                    ┌──────────────────────┐
                    │  WordPress Database  │
                    │  (ca_* tables)       │
                    └──────────────────────┘
```

## Request Flow

### 1. REST API Request

```
External Client (AI Bot)
    │
    │ POST /wp-json/v-wpsa/v1/report
    │ {"domain":"example.com"}
    │
    v
WordPress REST API
    │
    │ Authentication Check
    │ (manage_options)
    │
    v
V_WPSA_Rest_API::get_report()
    │
    │ Extract parameters
    │
    v
V_WPSA_Report_Service::prepare_report()
    │
    ├─> Domain validation
    ├─> Cache checking
    ├─> Analysis (if needed)
    ├─> PDF generation
    └─> JSON assembly
    │
    v
Return JSON Response
    │
    │ {score: 85, pdf_url: "...", report: {...}}
    │
    v
External Client receives data
```

### 2. PHP Helper Request

```
WordPress Plugin/Theme Code
    │
    │ v_wpsa_get_report_data('example.com')
    │
    v
V_WPSA_Report_Service::prepare_report()
    │
    ├─> Domain validation
    ├─> Cache checking
    ├─> Analysis (if needed)
    ├─> PDF generation
    └─> JSON assembly
    │
    v
Return Array/WP_Error
    │
    │ ['score' => 85, 'pdf_url' => "...", ...]
    │
    v
Plugin code processes result
```

### 3. AJAX Request (Existing - Refactored)

```
Browser (Shortcode Page)
    │
    │ POST admin-ajax.php
    │ action=v_wpsa_generate_report
    │
    v
V_WPSA_Ajax_Handlers::generate_report()
    │
    │ Nonce verification
    │
    v
V_WPSA_Report_Service::prepare_report()
    │
    ├─> Domain validation
    ├─> Cache checking
    ├─> Analysis (if needed)
    ├─> PDF generation
    └─> JSON assembly
    │
    v
V_WPSA_Report_Generator::generate_html_report()
    │
    │ Render template with data
    │
    v
Return HTML Response
    │
    │ {html: "<div>...</div>", nonce: "..."}
    │
    v
Browser updates DOM with report
```

## Data Flow

```
Input Domain
    ↓
┌─────────────────────┐
│ Domain Validation   │ → Invalid? → Return Error
│ V_WPSA_Validation   │
└─────────────────────┘
    ↓ Valid
┌─────────────────────┐
│ Check Database      │ → Not Found? ────┐
│ V_WPSA_DB           │                   │
└─────────────────────┘                   │
    ↓ Found                               │
┌─────────────────────┐                   │
│ Check Cache Age     │ → Stale? ─────────┤
│ (24 hours default)  │                   │
└─────────────────────┘                   │
    ↓ Fresh                               │
┌─────────────────────┐                   │
│ Use Cached Data     │                   │
└─────────────────────┘                   │
    ↓                                     ↓
    │                          ┌─────────────────────┐
    │                          │ Analyze Website     │
    │                          │ V_WPSA_DB::analyze  │
    │                          └─────────────────────┘
    │                                     ↓
    │                          ┌─────────────────────┐
    │                          │ Save to Database    │
    │                          └─────────────────────┘
    │                                     │
    └──────────────┬──────────────────────┘
                   ↓
        ┌─────────────────────┐
        │ Get Full Report Data│
        │ V_WPSA_DB           │
        └─────────────────────┘
                   ↓
        ┌─────────────────────┐
        │ Generate PDF        │ → PDF exists? → Use cached
        │ V_WPSA_Report_Gen   │
        └─────────────────────┘
                   ↓ New PDF
        ┌─────────────────────┐
        │ Assemble JSON       │
        │ Payload             │
        └─────────────────────┘
                   ↓
        ┌─────────────────────┐
        │ Return Data         │
        │ (Array or WP_Error) │
        └─────────────────────┘
```

## Component Interaction

```
┌───────────────────────────────────────────────────────────────┐
│                    ACCESS LAYER                                │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │ REST API Endpoint                                     │    │
│  │ - Authentication: Admin required                      │    │
│  │ - Input: JSON {domain, force}                         │    │
│  │ - Output: JSON {score, pdf_url, report, ...}          │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │ PHP Helper Function                                   │    │
│  │ - Authentication: None (trusted context)              │    │
│  │ - Input: string $domain, array $args                  │    │
│  │ - Output: array or WP_Error                           │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │ AJAX Handler (Refactored)                             │    │
│  │ - Authentication: Nonce                               │    │
│  │ - Input: POST {domain, nonce, force}                  │    │
│  │ - Output: JSON {html, nonce}                          │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
└────────────────────┬───────────────────────────────────────────┘
                     │
                     │ All call same service
                     │
                     v
┌───────────────────────────────────────────────────────────────┐
│                   SERVICE LAYER (NEW)                          │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │ V_WPSA_Report_Service                                 │    │
│  │                                                        │    │
│  │ prepare_report($domain_raw, $args)                    │    │
│  │                                                        │    │
│  │ Responsibilities:                                      │    │
│  │ • Normalize inputs                                     │    │
│  │ • Validate domain                                      │    │
│  │ • Check cache                                          │    │
│  │ • Trigger analysis                                     │    │
│  │ • Ensure PDF exists                                    │    │
│  │ • Assemble response                                    │    │
│  │ • Handle errors                                        │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
└────────────────────┬───────────────────────────────────────────┘
                     │
                     │ Delegates to existing components
                     │
                     v
┌───────────────────────────────────────────────────────────────┐
│                  EXISTING COMPONENTS                           │
│                                                                │
│  V_WPSA_Validation  →  Domain validation                      │
│  V_WPSA_DB          →  Database operations & analysis         │
│  V_WPSA_Report_Gen  →  HTML & PDF generation                  │
│  V_WPSA_Helpers     →  Utility functions                      │
│                                                                │
└───────────────────────────────────────────────────────────────┘
```

## Key Benefits of This Architecture

### 1. Single Source of Truth
- All report generation flows through `V_WPSA_Report_Service`
- No duplicated logic between AJAX and REST
- Easier to maintain and test

### 2. Separation of Concerns
- **Access Layer**: Handles authentication and I/O format
- **Service Layer**: Contains business logic
- **Data Layer**: Manages persistence

### 3. Backward Compatibility
- Existing AJAX functionality unchanged
- Templates still work the same
- Database schema unchanged
- No migration needed

### 4. Extensibility
- Easy to add new access methods (GraphQL, CLI, etc.)
- New features added once in service layer
- All interfaces benefit automatically

### 5. Testability
- Service layer can be tested independently
- Mock inputs/outputs easily
- Clear interfaces between components

## Security Architecture

```
External Request
    │
    v
┌─────────────────────────────────────┐
│ WordPress REST API                   │
│ - Nonce/Cookie verification         │
│ - Application password auth          │
└─────────────────────────────────────┘
    │
    v
┌─────────────────────────────────────┐
│ Permission Check                     │
│ - current_user_can('manage_options')│
│ - Customizable via filter            │
└─────────────────────────────────────┘
    │
    v
┌─────────────────────────────────────┐
│ Input Sanitization                   │
│ - V_WPSA_Validation::validate_domain│
│ - sanitize_text_field()              │
└─────────────────────────────────────┘
    │
    v
┌─────────────────────────────────────┐
│ Business Logic                       │
│ - V_WPSA_Report_Service             │
└─────────────────────────────────────┘
    │
    v
┌─────────────────────────────────────┐
│ Database Operations                  │
│ - Prepared statements                │
│ - Escaped queries                    │
└─────────────────────────────────────┘
    │
    v
┌─────────────────────────────────────┐
│ Output                               │
│ - JSON response (sanitized)          │
│ - WP_Error for failures              │
└─────────────────────────────────────┘
```

## Performance Considerations

### Caching Strategy

```
Request arrives
    │
    v
Check database ────→ Not found → Analyze → Save → Return
    │ Found
    v
Check timestamp
    │
    ├─→ < 24h old → Return cached
    │
    └─→ > 24h old → Delete → Analyze → Save → Return
```

### PDF Generation

```
Need PDF?
    │
    v
Check file exists ───→ Not found → Generate → Save → Return URL
    │ Found
    v
Check timestamp
    │
    ├─→ < 24h old → Return cached URL
    │
    └─→ > 24h old → Generate → Save → Return URL
```

## Integration Points

```
┌─────────────────────────────────────────────────────────┐
│                  YOUR SYSTEM                             │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ AI Chatbot   │  │ Dashboard    │  │ CRM System   │ │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘ │
│         │                  │                  │          │
└─────────┼──────────────────┼──────────────────┼─────────┘
          │                  │                  │
          │ REST API         │ PHP Helper       │ REST API
          │                  │                  │
┌─────────┼──────────────────┼──────────────────┼─────────┐
│         v                  v                  v          │
│                  v-wpsa Plugin                           │
│                                                          │
│      V_WPSA_Report_Service::prepare_report()            │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

## Summary

This architecture provides:
- ✓ Clean separation of concerns
- ✓ Single source of truth for business logic
- ✓ Multiple access methods (REST, PHP, AJAX)
- ✓ Full backward compatibility
- ✓ Easy to extend and maintain
- ✓ Comprehensive error handling
- ✓ Proper authentication and security
- ✓ Intelligent caching strategy
- ✓ Production-ready code quality

The service layer pattern ensures that all future enhancements benefit all access methods automatically, making the codebase maintainable and scalable.
