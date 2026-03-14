# Figma Make Internal Interpretation Guide

This reference exists only to help skills interpret Figma Make input safely.

## Purpose

- infer structural intent from Figma Make output
- separate semantic sections from presentational details
- avoid direct markup translation into WordPress

## Rules

1. Treat Figma Make code as descriptive input, not deployable output.
2. Prefer section intent over local DOM shape.
3. Preserve content hierarchy before styling fidelity.
4. Degrade unsupported interactions or layout features explicitly.
5. Record every non-trivial adaptation in the page report.

## Anti-patterns

- copying React or HTML output into Gutenberg verbatim
- creating custom blocks to compensate for weak reasoning
- treating visual parity as more important than whitelist compliance
