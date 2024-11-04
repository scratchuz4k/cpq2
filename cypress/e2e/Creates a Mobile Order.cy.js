const crypto = require("crypto");
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}
// Function to generate hash
const generateHash = (input) => {
    return crypto.createHash("md5").update(input).digest("hex"); // SHA-256 hash
}
const getCurrentDate = () => {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, "0");
    const day = String(currentDate.getDate()).padStart(2, "0");
    const hour = String(currentDate.getHours()).padStart(2, "0");
    const minute = String(currentDate.getMinutes()).padStart(2, "0");
    const millisecond = String(currentDate.getMilliseconds()).padStart(3, "0");

    // Create a readable date string
    const readableDate = `${year}-${month}-${day} ${hour}:${minute}:${millisecond}`;

    return readableDate;
}
const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }
const quantities = []


describe("Creates a Mobile Order", () => {
    let hardwareCount = 0;

    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })
    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it("Creates Mobile Order", () => {
        cy.visit("/")
        cy.additems(account.name)
    })
    it("I can change Margin and Discount", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()

        let items = ["recurring", "one_off"]

        items.forEach((key) => {
            let total = null
            cy.get("#" + key + "_total").then(($title) => {
                total = $title.text().trim();
            });

            let line1 = { qty: null, discount: null, margin: null }
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").then(($input) => {
                line1.qty = $input.val().trim();
            }).clear().type(rand(line1.qty));
            cy.get("#input-" + key + "-0-discount").then(($input) => {
                line1.discount = $input.val().trim();
            }).clear().type(rand(line1.discount, 11));
            cy.get("#input-" + key + "-0-margin").then(($input) => {
                line1.margin = $input.val().trim();
            }).clear().type(rand(line1.margin, 21));


            cy.get("#btn-" + key + "-0-save").click()
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.qty);
                });
            cy.get("#input-" + key + "-0-discount").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.discount);
                });
            cy.get("#input-" + key + "-0-margin").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.margin);
                });

            cy.get("#" + key + "_total").invoke("text")
                .then((text) => {
                    expect(text).to.not.equal(total);
                });
        })
    })

    it("I can change and reset Global Discount", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        let order = { margin: null, comission: null, global: null }

        cy.get("#input-margin-order").then(($input) => {
            order.margin = $input.val().trim();
        })
        cy.get("#input-global-discount").then(($input) => {
            order.global = $input.val().trim();
        }).clear().type(rand(order.global));

        cy.get("#btn-apply-global-discount").click()
        cy.get("#btn-apply-global-discount", { timeout: 3000 }).should("not.be.disabled");

        cy.get("#input-margin-order").invoke("val")
            .then((val) => {
                expect(val).to.not.equal(order.margin);
            });

        cy.get("#btn-reset-quote").click()
        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
        cy.get("#btn-reset-quote", { timeout: 3000 }).should("not.be.disabled");
        cy.get("#input-margin-order").invoke("val")
            .then((val) => {
                expect(val).to.equal("0.00 %");
            });
    })
    it("I can add and delete a Custom Line", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#btn-recurring-add").click()
        cy.get("#input-new-recurring-name").type("Custom line recurring")
        cy.get("#input-new-recurring-qty").type(10)
        cy.get("#input-new-recurring-price_monthly").type(10)
        cy.get("#input-new-recurring-discount").type(10)
        cy.get("#input-new-recurring-margin").type(20)
        cy.get("#btn-new-recurring-add").click()
        cy.get("#table-recurring").contains("Custom line recurring")

        cy.get("#btn-one_off-add").click()
        cy.get("#input-new-one_off-name").type("Custom line one_off")
        cy.get("#input-new-one_off-qty").type(10)
        cy.get("#input-new-one_off-price").type(10)
        cy.get("#input-new-one_off-discount").type(10)
        cy.get("#input-new-one_off-margin").type(20)
        cy.get("#btn-new-one_off-add").click()
        cy.get("#table-one_off").contains("Custom line one_off")

        cy.get(".recurring-item")
            .its("length")
            .then(count => {
                cy.get(`#btn-recurring-${count - 1}-remove`).click()
            });

        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
        cy.get(".one_off-item")
            .its("length")
            .then(count => {
                cy.get(`#btn-one_off-${count - 1}-remove`).click()
            });
        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
    })
});

