# Authorization Model — RBAC

**Phase:** 4 — Architecture
**Status:** Decided
**Inputs:** saba.md §10.2, `docs/product-requirements.md` §6, `docs/architecture/adr-001-inertia-vs-api-spa.md` §3.6

---

## 1. Resolving the Teams Scaffold Question (ADR-001 §5 follow-up)

The existing scaffold's `Team` / `Membership` / `TeamInvitation` models and `TeamRole` (Owner/Admin/Member) / `TeamPermission` enums implement **multi-tenant workspace membership** — many teams, each with their own members and roles, users able to belong to several teams and switch between them (`current_team_id` on `users`, `{current_team}` route prefix in `routes/web.php`).

Saba International is one organization with one fixed staff/board admin roster. There are no "teams" to switch between, no invitation-based workspace creation, no per-team billing or isolation. Keeping the Team model would mean either (a) creating a single permanent "team" every install and pretending the multi-tenancy layer isn't there, which just adds indirection with no payoff, or (b) actually using multi-tenancy for something Saba doesn't need.

**Decision: drop the `Team`/`Membership`/`TeamInvitation` multi-tenancy layer. Keep the enum-based role/permission *pattern*** (`TeamRole`/`TeamPermission` → `AdminRole`/`AdminPermission`), because that pattern — a closed enum of roles, each mapped to a fixed permission set via a `permissions(): array` method, checked through `hasPermission()` — is a good fit for saba.md §10.2's requirement of a small, fixed set of roles with clearly bounded permissions. It's just applied directly to `User`, not routed through a `Team`.

This means:
- Remove `current_team_id` from `users`, drop `{current_team}` route-prefix middleware, drop `EnsureTeamMembership`.
- Add `admin_role` (nullable enum) directly on `users`. `null` = not an admin account (shouldn't exist in V1 — see §4; reserved for if public accounts are ever introduced, e.g., a future Donor Portal per saba.md §34.3).
- `TeamInvitation`'s underlying pattern (an invitation record with a signed code, expiry, acceptance tracking) is still useful for **admin user provisioning** (§4 below) — reused as `UserInvitation`, not deleted outright.

---

## 2. V1 Roles

Per `docs/product-requirements.md` §6, V1 ships 4 of saba.md §10.2's 7 roles — the ones that enforce a real separation of concern, not further subdivisions of "can edit content":

| Role | Maps to saba.md §10.2 | Scope |
|---|---|---|
| **Super Administrator** | Super Administrator + Administrator (merged) | Full access: content, users, roles, settings, integrations, audit logs, financial data |
| **Editor** | Editor + Content Manager + Communications Manager (merged) | All content types (pages, programs, stories, team, media, documents, newsletter, contact, volunteer, partnership) — **no** access to donations/transactions/supporters |
| **Finance Manager** | Finance Manager (unchanged) | Donations, transactions, supporters, campaigns — **no** content editing |
| **Viewer** | Viewer (unchanged) | Read-only across everything Super Administrator can see, no writes |

**Deferred to V2** (per product-requirements.md §6): splitting Editor into Content Manager (no newsletter/contact access) and Communications Manager (no program/page editing) — add only if a real need for that finer split shows up once there's more than one or two people in the Editor role day to day.

**Deviation from saba.md §12.1's schema:** saba.md lists `roles`, `permissions`, `role_permission`, `user_role` as separate tables — i.e., a fully dynamic, admin-configurable RBAC system where roles and permissions are database rows. V1 does not build this. Four fixed roles with fixed permission sets, defined as PHP enums, is enough for an organization with a handful of admin users and no stated need to invent custom roles. A dynamic roles/permissions system is real, non-trivial admin UI (saba.md §10.1's System → Roles → Permissions screens) for a capability nobody has asked for yet — building it now would violate the "don't design for hypothetical future requirements" principle. If Saba ever needs custom roles, this is a bounded, well-understood migration (enum → DB-backed roles), not a rewrite.

---

## 3. Permission Matrix

Permissions are grouped by the admin module areas from saba.md §10.1:

| Permission | Super Administrator | Editor | Finance Manager | Viewer |
|---|:---:|:---:|:---:|:---:|
| View content (pages, programs, stories, team, media, documents) | ✅ | ✅ | ✅ | ✅ |
| Create/edit/publish content | ✅ | ✅ | ❌ | ❌ |
| Delete/archive content | ✅ | ✅ | ❌ | ❌ |
| Manage impact metrics & reports | ✅ | ✅ | ❌ | ✅ (view only) |
| View newsletter subscribers, contact messages, volunteer/partnership inquiries | ✅ | ✅ | ❌ | ✅ (view only) |
| Respond to / update status of contact-type submissions | ✅ | ✅ | ❌ | ❌ |
| View donations, transactions, supporters | ✅ | ❌ | ✅ | ✅ (view only) |
| Manage campaigns | ✅ | ❌ | ✅ | ❌ |
| Change donation/transaction status (e.g., mark refunded) | ✅ | ❌ | ✅ | ❌ |
| Export donor data | ✅ | ❌ | ✅ | ❌ |
| Manage users & roles | ✅ | ❌ | ❌ | ❌ |
| Manage system settings & integrations | ✅ | ❌ | ❌ | ❌ |
| View audit logs | ✅ | ❌ | ❌ | ❌ |

This is a direct implementation of saba.md §10.2's Principle of Least Privilege statement: *"Finance Manager cannot modify website content. Content Manager cannot access donor financial information."* — enforced here as Editor having zero row-level or column-level access to `donations`/`donation_transactions`/`supporters`, and Finance Manager having zero write access to any content table.

---

## 4. Admin User Provisioning

**No public registration for admin accounts.** The starter kit ships with a working `/register` flow (Fortify `CreateNewUser` action, `Register.vue` page) intended for the Teams SaaS use case (anyone signs up, creates or joins a team). That's wrong for a nonprofit's internal CMS — admin accounts must be provisioned deliberately, not self-served.

**Decision:** disable public registration entirely (`Features::registration()` off in Fortify config, or the route simply not registered). New admin users are created one of two ways:
1. A Super Administrator creates the account directly from the admin panel (User Management, saba.md §10.1), setting their role at creation time.
2. A Super Administrator sends an invitation (reusing the `TeamInvitation` pattern per §1 above, renamed `UserInvitation`) — an emailed, expiring, single-use signed link that lets the invitee set their own password and complete MFA enrollment, without ever exposing an open sign-up form.

Either path ends at the same required step: **MFA enrollment is mandatory before the account can access any admin route** (saba.md §10.4) — see `docs/architecture/authentication.md` §3 for the enforcement mechanism.

---

## 5. Enforcement Mechanism

Following the existing scaffold's own pattern (`TeamRole::hasPermission()`) rather than introducing a new paradigm:

```php
enum AdminRole: string
{
    case SuperAdministrator = 'super_administrator';
    case Editor = 'editor';
    case FinanceManager = 'finance_manager';
    case Viewer = 'viewer';

    public function permissions(): array { /* per matrix in §3 */ }
    public function hasPermission(AdminPermission $permission): bool { ... }
}
```

Laravel **Policies** (per saba.md §11.3's requirement that every controller has one) call `$user->admin_role->hasPermission(...)` rather than re-implementing role logic per-policy. This keeps the permission matrix in exactly one place (the enum), which is what makes the least-privilege guarantee in §3 actually verifiable — a policy can't accidentally drift from the matrix because it never encodes permission logic itself, only which permission a given action requires.

**Audit logging** (saba.md §10.3) hooks off the same policy layer: any action a policy authorizes that mutates state (publish, delete, change donation status, modify user permissions, export donor data, change campaign settings) writes an `AuditLog` row — see `docs/architecture/database-erd.md` for the table shape. This is scoped to Super Administrator, Editor, and Finance Manager actions; Viewer performs no mutations, so it never appears as an audit log actor for anything but login events.

---

## 6. What This Unblocks

- `docs/architecture/database-erd.md`'s `users` table can now be specified concretely (`admin_role` column, no `current_team_id`).
- `docs/architecture/authentication.md` can specify MFA enforcement against a known role set.
- Phase 5 backend work has a concrete enum and policy pattern to implement rather than an open question.
