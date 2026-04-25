<?php

use Models\BooksForLessons\BooksTable as Books;

class BooksTableReader
{
    public function getBooksCollectionItems(): array
    {
        $booksCollection = Books::getList([
            'select' => [
                '*',
                'AUTHORS',
                'STORES',
                'PUBLISHER',
                'WIKIPROFILE',
            ]
        ])->fetchCollection();

        if ($booksCollection === null) {
            return [];
        }

        $booksCollectionItems = [];

        foreach ($booksCollection as $bookItem) {
            $authors = [];
            foreach ($bookItem->getAuthors() as $author) {
                $authors[] = [
                    'AUTHOR_ID' => $author->getId(),
                    'AUTHOR_NAME' => $author->getName(),
                ];
            }

            $stores = [];
            foreach ($bookItem->getStores() as $store) {
                $stores[] = [
                    'STORE_ID' => $store->getId(),
                    'STORE_NAME' => $store->getName(),
                ];
            }

            $booksCollectionItems[] = [
                'ID' => $bookItem->getId(),
                'NAME' => $bookItem->getName(),
                'TEXT' => $bookItem->getText(),
                'PUBLISH_DATE' => $bookItem->getPublishDate()?->format('Y-m-d'),
                'ISBN' => $bookItem->getIsbn(),
                'AUTHORS' => $authors,
                'STORES' => $stores,
                'PUBLISHER_ID' => $bookItem->getPublisher()?->getId(),
                'PUBLISHER_NAME' => $bookItem->getPublisher()?->getName(),
                'WIKIPROFILE_ID' => $bookItem->getWikiprofile()?->getId(),
                'WIKIPROFILE_RU' => $bookItem->getWikiprofile()?->getWikiprofileRu(),
            ];
        }

        return $booksCollectionItems;
    }
}
