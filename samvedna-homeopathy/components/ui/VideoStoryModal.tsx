"use client";

import Image from "next/image";
import { Play } from "lucide-react";
import type { VideoTestimonial } from "@/types";
import { socialLinks } from "@/constants/site";
import Modal from "@/components/ui/Modal";
import { blurDataUrl } from "@/lib/utils";

const youtubeChannel =
  socialLinks.find((link) => link.label === "YouTube")?.href ??
  "https://www.youtube.com";

type VideoStoryModalProps = {
  /** The video to play, or null when the modal is closed. */
  video: VideoTestimonial | null;
  onClose: () => void;
};

export default function VideoStoryModal({ video, onClose }: VideoStoryModalProps) {
  return (
    <Modal
      open={video !== null}
      onClose={onClose}
      ariaLabel={video ? `${video.name} video story` : "Video story"}
      className="max-w-3xl p-0"
    >
      {video ? (
        <div>
          <div className="aspect-video w-full overflow-hidden rounded-t-3xl bg-black">
            {video.youtubeId ? (
              <iframe
                key={video.youtubeId}
                src={`https://www.youtube-nocookie.com/embed/${video.youtubeId}?autoplay=1&rel=0&modestbranding=1`}
                title={`${video.name} — ${video.condition}`}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowFullScreen
                className="h-full w-full border-0"
              />
            ) : (
              <div className="relative h-full w-full">
                <Image
                  src={video.poster}
                  alt={video.alt}
                  fill
                  sizes="(min-width: 768px) 768px, 100vw"
                  className="object-cover opacity-60"
                  placeholder="blur"
                  blurDataURL={blurDataUrl}
                />
                <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-slate-900/50 p-6 text-center">
                  <p className="text-lg font-semibold text-white">
                    Full video coming soon
                  </p>
                  <a
                    href={youtubeChannel}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary hover:text-white"
                  >
                    <Play className="h-4 w-4 fill-current" aria-hidden="true" />
                    Watch on YouTube
                  </a>
                </div>
              </div>
            )}
          </div>

          <div className="p-6">
            <p className="font-bold text-text">{video.name}</p>
            <p className="mt-1 text-sm font-medium text-muted">
              {video.condition}
              <span className="mx-1.5 opacity-50">|</span>
              {video.location}
            </p>
          </div>
        </div>
      ) : null}
    </Modal>
  );
}
