import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };

const hexToRgb = (hex) =>
    `rgb(${hex
        .match(/[\da-f]{2}/gi)
        .map((v) => parseInt(v, 16))
        .join(', ')})`;

const appColors = {
    app: {
        header: 'ff0000',
        navbar: '00ff00',
        footer: '0000ff'
    },
    shareable: {
        header: 'ffff00',
        footer: '00ffff'
    }
};

let url = '';

describe('Add tenant links.cy', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Check default colors', () => {
        cy.visit('/quotes');
        cy.get('.bg-theme-secondary-dark').should('have.css', 'background-color', hexToRgb('1e1e1e'));
        cy.get('.bg-theme-secondary-light').should('have.css', 'background-color', hexToRgb('3d3d3d'));
        cy.get('.footer').should('have.css', 'background-color', hexToRgb('1e1e1e'));
    });

    it('Update Tenant colors and check', () => {
        cy.visit('/tenants');
        cy.get('#2-2').click();
        cy.get('#v-pills-colors-tab').click();

        // App colors
        Object.entries(appColors).forEach(([section, colors]) => {
            Object.entries(colors).forEach(([element, value]) => {
                cy.get(`#color-${section}-${element}-hex`).clear().type(value);
            });
        });

        cy.visit('/quotes');

        cy.get('.bg-theme-secondary-dark').should('have.css', 'background-color', hexToRgb(appColors.app.header));
        cy.get('.bg-theme-secondary-light').should('have.css', 'background-color', hexToRgb(appColors.app.navbar));
        cy.get('.footer').should('have.css', 'background-color', hexToRgb(appColors.app.footer));
    });

    it('Create quote and a shared link', () => {
        cy.visit('/quotes/create');
        cy.selectLazyOption('account_id');
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-shareable-open').click();
        cy.get('#btn-shareable-create').click();
        cy.get(`#input-shareable-link`)
            .invoke('val')
            .then((value) => {
                cy.log(value);
                url = value;
            });
    });

    it('Access the shareablelink and check colors', () => {
        cy.logout();
        cy.visit('/quotes');
        cy.log(url);
        cy.visit(url);

        cy.get('.bg-theme-secondary-dark').should('have.css', 'background-color', hexToRgb(appColors.shareable.header));
        cy.get('.footer').should('have.css', 'background-color', hexToRgb(appColors.shareable.footer));
    });
});
