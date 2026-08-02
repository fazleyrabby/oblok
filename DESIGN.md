# DESIGN.md

# Project Atlas — Design System

> This document defines the visual language, design principles, UI components, interaction patterns, and accessibility standards for Project Atlas.
>
> It serves as the single source of truth for both human designers and AI coding agents when creating or modifying the user interface.
>
> **This is the Design System** (tokens, colors, typography, components). For UX patterns, user flows, and interaction behavior, see [docs/design.md](docs/design.md).

---

# Design Philosophy

Atlas is a professional developer tool.

The interface should prioritize:

- Clarity
- Density
- Speed
- Consistency
- Accessibility

The UI should help users complete operational tasks quickly without unnecessary visual distractions.

Design should feel intentional, calm, and production-ready.

---

# Design Principles

## Functional First

Every UI element must have a purpose.

Avoid decorative elements that do not improve usability.

---

## Consistency

The same interaction should behave the same way throughout the application.

---

## Density

Atlas is built for engineers.

Information density is preferred over excessive whitespace.

Users should see more data with less scrolling.

---

## Progressive Disclosure

Only reveal advanced functionality when needed.

Keep the default interface simple.

---

## Accessibility

Every feature should remain usable with:

- Keyboard navigation
- Screen readers
- High contrast
- Reduced motion

Accessibility is not optional.

---

# Visual Style

Atlas should feel similar to modern engineering tools.

Primary inspiration:

- Linear
- GitHub
- Vercel
- Raycast
- Railway
- Tabler

Avoid:

- Glassmorphism
- Excessive gradients
- Neon colors
- Oversized rounded corners
- Heavy shadows
- Decorative animations

---

# Theme

Default:

Dark Mode

Secondary:

Light Mode

Dark mode should always receive equal attention.

---

# Color Philosophy

Use semantic colors.

Do not rely on color alone to communicate state.

Primary

Blue

Success

Green

Warning

Amber

Danger

Red

Info

Sky

Muted

Gray

Every status should also include an icon or label.

---

# Typography

Font

Geist

Fallback

Inter

System UI

Hierarchy

H1

Page titles

H2

Section titles

H3

Card titles

Body

Readable documentation

Code

JetBrains Mono

---

# Spacing

Use an 8-point spacing system.

Allowed spacing values

4

8

12

16

24

32

48

64

Avoid arbitrary spacing.

---

# Border Radius

Minimal.

Cards

12px

Buttons

10px

Inputs

10px

Badges

999px

Avoid overly rounded components.

---

# Shadows

Subtle only.

Cards should rely more on borders than shadows.

---

# Icons

Use Lucide Icons.

Icons should always accompany important actions.

Avoid emoji in the application UI.

---

# Layout

Desktop-first.

Layout

Header

↓

Sidebar

↓

Main Content

↓

Right Utility Panel (future)

Content width should remain readable on ultra-wide displays.

---

# Navigation

Sidebar navigation.

Support:

- Collapse
- Search
- Keyboard shortcuts
- Active indicators

Navigation should remain consistent across every page.

---

# Cards

Cards are used to group related information.

Every card should have:

- Title
- Optional description
- Actions
- Content

Avoid deeply nested cards.

---

# Tables

Tables are first-class citizens.

Support:

- Search
- Filters
- Sorting
- Pagination
- Bulk actions
- Sticky headers
- Responsive overflow

Large datasets should remain easy to scan.

---

# Forms

Forms should:

- Validate inline
- Clearly explain errors
- Show loading states
- Preserve entered values when validation fails

Required fields should be clearly marked.

---

# Buttons

Primary

Main action

Secondary

Alternative action

Ghost

Low emphasis

Danger

Destructive action

Loading states are required.

---

# Empty States

Every empty state should include:

- Illustration or icon
- Short explanation
- Recommended next action

Avoid blank pages.

---

# Loading States

Prefer skeleton loaders over spinners.

Long-running operations should display progress when possible.

---

# Notifications

Support:

- Success
- Warning
- Error
- Information

Notifications should disappear automatically unless user action is required.

---

# Dialogs

Confirmation dialogs are required for destructive actions.

Escape key should close dialogs.

Focus should remain trapped inside open dialogs.

---

# Search

Search should be globally accessible.

Eventually support:

⌘K / Ctrl+K command palette.

---

# Charts

Use ApexCharts.

Charts should:

- Be interactive
- Support dark mode
- Include legends
- Display tooltips
- Avoid unnecessary animations

---

# Data Visualization

Prefer:

- Line Charts
- Area Charts
- Bar Charts
- Donut Charts

Avoid:

- 3D charts
- Pie charts with many slices

---

# Animations

Animation should communicate state.

Duration

150–250ms

Avoid bounce animations.

Respect "prefers-reduced-motion".

---

# Responsive Design

Support:

Desktop

Tablet

Mobile

Dashboard widgets should reflow gracefully.

---

# Accessibility

Target WCAG AA.

Requirements

- Keyboard navigation
- Focus indicators
- Color contrast
- Semantic HTML
- ARIA labels where appropriate

---

# Component Library

Primary Components

- Button
- Input
- Select
- Textarea
- Checkbox
- Radio
- Switch
- Badge
- Alert
- Card
- Table
- Modal
- Drawer
- Tabs
- Dropdown
- Tooltip
- Toast
- Avatar
- Breadcrumb
- Pagination

Every component should have a consistent API.

---

# Design Rules for AI Agents

When generating UI:

- Reuse existing components.
- Do not introduce new visual patterns unless approved.
- Maintain consistent spacing.
- Prefer composition over custom styling.
- Keep interfaces simple.
- Optimize for readability.
- Avoid visual noise.

When in doubt:

Choose the simpler solution.

---

# Future Design Goals

- Fully documented component library
- Design tokens
- Theme customization
- Command Palette
- Keyboard-first workflows
- Data-dense dashboard layouts
- Plugin-aware navigation
- Mobile companion interface

---

# Guiding Principle

Atlas should feel like software built by engineers for engineers.

Every interface should reduce cognitive load, surface the right information at the right time, and make operational workflows fast, predictable, and enjoyable.