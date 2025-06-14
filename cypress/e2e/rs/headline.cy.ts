/// <reference types="cypress" />

// Welcome to Cypress!
//
// This spec file contains a variety of sample tests
// for a todo list app that are designed to demonstrate
// the power of writing tests in Cypress.
//
// To learn more about how Cypress works and
// what makes it such an awesome testing tool,
// please read our getting started guide:
// https://on.cypress.io/introduction-to-cypress

import type { Result } from 'axe-core';

function logA11yViolations(violations: Result[]) {
  cy.task('log', `${violations.length} accessibility violation${violations.length === 1 ? '' : 's'} ${violations.length === 1 ? 'was' : 'were'} detected`);
  // pluck specific keys to keep the table readable
  const violationData = violations.map(({ id, impact, description, nodes }) => ({
    id,
    impact,
    description,
    nodes: nodes.length,
  }));

  cy.task('table', violationData);
}

describe('teste die Startseite', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('/');
    cy.injectAxe({ axeCorePath: 'node_modules/axe-core/axe.min.js' });
    cy.wait(1000);
    cy.screenshot();
  });

  it('prüfe Headline', () => {
    cy.screenshot();
    //cy.get('.sxr__headline-calculator span').should('have.length', 1)
    cy.get('.card-header').first().should('have.text', 'Alttarifbewertung');
  });

  it('checkA11y', () => {
    //cy.get('.sxr__headline-calculator span').should('have.length', 1)
    cy.checkA11y(undefined, undefined, logA11yViolations, true);
  });
});
