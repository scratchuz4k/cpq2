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
describe('Add tenant links.cy', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Check default colors', () => {
        cy.visit('/quotes');
        cy.get('.bg-theme-secondary-dark').should('have.css', 'background-color', hexToRgb('aaaaaa'));
        cy.get('.bg-theme-secondary-light').should('have.css', 'background-color', hexToRgb('eeeeee'));
        cy.get('.footer').should('have.css', 'background-color', hexToRgb('aaaaaa'));
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
});
