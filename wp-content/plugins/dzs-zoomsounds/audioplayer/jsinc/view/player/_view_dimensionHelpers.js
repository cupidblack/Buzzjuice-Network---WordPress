export function view_calculateTargetPositionOnScrub(selfClass, scrubbarWidth, e) {
  let targetPositionOnScrub = ((e.pageX - (selfClass._scrubbar.offset().left)) / (scrubbarWidth) * selfClass.timeModel.getVisualTotalTime());


  if (selfClass.sample_time_start > 0) {
    targetPositionOnScrub -= selfClass.sample_time_start;
  }


  return targetPositionOnScrub;
}
