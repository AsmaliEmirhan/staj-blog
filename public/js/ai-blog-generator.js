(() => {
    'use strict';

    const initializeAiBlogGenerator = () => {
        const generator = document.querySelector('#ai-blog-generator');

        if (!generator) {
            return;
        }

        const keywordsInput = document.querySelector('#ai-keywords');
        const contentInput = document.querySelector('#content');
        const generateButton = document.querySelector(
            '#generate-blog-button',
        );
        const statusElement = document.querySelector(
            '#ai-generation-status',
        );
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (
            !keywordsInput
            || !contentInput
            || !generateButton
            || !statusElement
            || !csrfToken
        ) {
            return;
        }

        const endpoint = generator.dataset.endpoint;
        const defaultButtonText = generateButton.textContent;

        const showStatus = (message, isError = false) => {
            statusElement.textContent = message;
            statusElement.classList.toggle('field-error', isError);
        };

        generateButton.addEventListener('click', async () => {
            const keywords = keywordsInput.value.trim();

            if (keywords === '') {
                showStatus(
                    'Lütfen en az bir anahtar kelime girin.',
                    true,
                );

                keywordsInput.focus();

                return;
            }

            if (
                contentInput.value.trim() !== ''
                && !window.confirm(
                    'Mevcut içerik AI tarafından üretilen metinle değiştirilecek. Devam etmek istiyor musunuz?',
                )
            ) {
                return;
            }

            generateButton.disabled = true;
            generateButton.textContent = 'Üretiliyor...';
            showStatus('Blog içeriği hazırlanıyor.');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        keywords,
                    }),
                });

                const result = await response
                    .json()
                    .catch(() => ({}));

                if (!response.ok) {
                    const validationMessage =
                        result.errors?.keywords?.[0];

                    throw new Error(
                        validationMessage
                        ?? result.message
                        ?? 'İçerik üretme isteği tamamlanamadı.',
                    );
                }

                const generatedContent = result.data?.content;

                if (
                    typeof generatedContent !== 'string'
                    || generatedContent.trim() === ''
                ) {
                    throw new Error(
                        'AI servisi geçerli bir içerik döndürmedi.',
                    );
                }

                contentInput.value = generatedContent.trim();
                contentInput.dispatchEvent(
                    new Event('input', {
                        bubbles: true,
                    }),
                );

                showStatus(
                    'İçerik üretildi. Kaydetmeden önce metni kontrol edin.',
                );

                contentInput.focus();
            } catch (error) {
                showStatus(
                    error instanceof Error
                        ? error.message
                        : 'Beklenmeyen bir hata oluştu.',
                    true,
                );
            } finally {
                generateButton.disabled = false;
                generateButton.textContent = defaultButtonText;
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initializeAiBlogGenerator,
        );
    } else {
        initializeAiBlogGenerator();
    }
})();